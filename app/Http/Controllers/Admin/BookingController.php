<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Coupon;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Setting;
use App\Models\User;
use App\Services\CouponService;
use App\Services\RoomPricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function __construct(
        protected RoomPricingService $pricing,
        protected CouponService $coupons
    ) {}

    public function index(Request $request)
    {
        $query = Booking::with(['user', 'room', 'roomType']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('booking_reference', 'like', "%{$s}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$s}%")
                                                     ->orWhere('email', 'like', "%{$s}%")
                                                     ->orWhere('phone', 'like', "%{$s}%"));
            });
        }

        $bookings = $query->latest()->paginate(15)->withQueryString();
        $roomTypes = RoomType::where('is_active', true)->orderBy('name')->get();

        $facilities = \App\Models\Facility::where('is_active', true)
            ->where('pricing_type', 'fixed')
            ->orderBy('sort_order')
            ->get(['id', 'name', 'price']);

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'roomTypes' => $roomTypes,
            'facilities' => $facilities,
            'filters' => $request->only(['status', 'search']),
            'depositDefault' => (float) config('hotel.deposit_percentage', 20),
        ]);
    }

    public function checkAvailability(Request $request)
    {
        $data = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'exclude_booking_id' => 'nullable|exists:bookings,id',
        ]);

        $bookedRoomIds = Booking::where('room_type_id', $data['room_type_id'])
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->where('check_in', '<', $data['check_out'])
            ->where('check_out', '>', $data['check_in'])
            ->when($request->filled('exclude_booking_id'), fn ($q) => $q->where('id', '!=', $data['exclude_booking_id']))
            ->pluck('room_id');

        $rooms = Room::where('room_type_id', $data['room_type_id'])
            ->where('is_active', true)
            ->whereNotIn('id', $bookedRoomIds)
            ->orderBy('room_number')
            ->get(['id', 'room_number', 'floor']);

        return response()->json(['rooms' => $rooms]);
    }

    public function calculateRate(Request $request)
    {
        $data = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'guests_count' => 'required|integer|min:1',
            'facilities' => 'nullable|array',
            'facilities.*.facility_id' => 'required_with:facilities|exists:facilities,id',
            'facilities.*.quantity' => 'required_with:facilities|integer|min:1|max:20',
            'coupon_id' => 'nullable|exists:coupons,id',
            'discount_amount' => 'nullable|numeric|min:0',
            'deposit_percentage' => 'nullable|numeric|min:0|max:100',
            'deposit_amount' => 'nullable|numeric|min:0',
        ]);

        $roomType = RoomType::findOrFail($data['room_type_id']);

        if ($data['guests_count'] > $roomType->max_capacity) {
            return response()->json(['message' => "Max capacity for this room type is {$roomType->max_capacity}."], 422);
        }

        $pricing = $this->pricing->calculateForStay($roomType, $data['check_in'], $data['check_out'], $data['guests_count']);
        $facilities = $this->resolveFacilities($data);

        [$deposit, $due, $totals] = $this->computeFinancials($pricing['total'], $facilities['subtotal'], $data);

        return response()->json([
            'nights' => $pricing['nights'],
            'breakdown' => $pricing['breakdown'],
            'facility_lines' => $facilities['lines'],
            'deposit_amount' => $deposit,
            'due_amount' => $due,
            ...$totals,
        ]);
    }
    public function applyCoupon(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        try {
            $result = $this->coupons->validateAndCalculate($data['code'], $data['amount']);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->validator->errors()->first()], 422);
        }

        return response()->json([
            'coupon_id' => $result['coupon']->id,
            'code' => $result['coupon']->code,
            'discount_amount' => $result['discount_amount'],
        ]);
    }

    public function searchCustomers(Request $request)
    {
        $q = $request->get('q', '');
        if (strlen($q) < 2) {
            return response()->json(['customers' => []]);
        }

        $customers = User::where('role', 'customer')
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%")
                      ->orWhere('phone', 'like', "%{$q}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'email', 'phone']);

        return response()->json(['customers' => $customers]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateBookingRequest($request);

        $booking = DB::transaction(function () use ($validated) {
            $userId = $this->resolveCustomer($validated);
            $roomType = RoomType::findOrFail($validated['room_type_id']);

            $this->assertRoomStillAvailable($validated['room_id'], $validated['room_type_id'], $validated['check_in'], $validated['check_out']);

            $pricing = $this->pricing->calculateForStay($roomType, $validated['check_in'], $validated['check_out'], $validated['guests_count']);
            $facilities = $this->resolveFacilities($validated);

            [$deposit, $due, $totals] = $this->computeFinancials($pricing['total'], $facilities['subtotal'], $validated);

            $booking = Booking::create([
                'booking_reference' => $this->generateReference(),
                'user_id' => $userId,
                'room_id' => $validated['room_id'],
                'room_type_id' => $validated['room_type_id'],
                'check_in' => $validated['check_in'],
                'check_out' => $validated['check_out'],
                'guests_count' => $validated['guests_count'],
                'status' => $validated['status'],
                'total_amount' => $totals['total_amount'],
                'subtotal' => $totals['subtotal'],
                'service_charge_percentage' => $totals['service_charge_percentage'],
                'service_charge_amount' => $totals['service_charge_amount'],
                'vat_percentage' => $totals['vat_percentage'],
                'vat_amount' => $totals['vat_amount'],
                'deposit_percentage' => $validated['deposit_percentage'] ?? config('hotel.deposit_percentage', 20),
                'deposit_amount' => $deposit,
                'due_amount' => $due,
                'coupon_id' => $validated['coupon_id'] ?? null,
                'discount_amount' => $totals['discount_amount'],
                'special_requests' => $validated['special_requests'] ?? null,
                'created_by' => auth()->id(),
            ]);

            if (!empty($facilities['lines'])) {
                $booking->facilities()->attach(
                    collect($facilities['lines'])->mapWithKeys(fn($line) => [
                        $line['facility_id'] => [
                            'quantity' => $line['quantity'],
                            'unit_price' => $line['unit_price'],
                            'subtotal' => $line['subtotal'],
                        ],
                    ])->toArray()
                );
            }

            if (!empty($validated['coupon_id'])) {
                Coupon::where('id', $validated['coupon_id'])->increment('used_count');
            }

            return $booking;
        });

        return response()->json([
            'message' => "Booking {$booking->booking_reference} created successfully.",
            'booking' => $booking->load(['user', 'room', 'roomType', 'facilities']),
        ]);
    }

    public function edit(Booking $booking)
    {
        $booking->load(['user', 'room', 'roomType', 'facilities']);

        return response()->json([
            'booking' => $booking,
            'editable' => in_array($booking->status, ['pending', 'confirmed']),
        ]);
    }

    public function update(Request $request, Booking $booking)
    {
        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return response()->json(['message' => 'This booking can no longer be edited.'], 422);
        }

        $validated = $this->validateBookingRequest($request, $booking->id);

        $booking = DB::transaction(function () use ($validated, $booking) {
            $userId = $this->resolveCustomer($validated, $booking->user_id);
            $roomType = RoomType::findOrFail($validated['room_type_id']);

            $this->assertRoomStillAvailable($validated['room_id'], $validated['room_type_id'], $validated['check_in'], $validated['check_out'], $booking->id);

            $pricing = $this->pricing->calculateForStay($roomType, $validated['check_in'], $validated['check_out'], $validated['guests_count']);
            $facilities = $this->resolveFacilities($validated);

            [$deposit, $due, $totals] = $this->computeFinancials($pricing['total'], $facilities['subtotal'], $validated);

            $booking->update([
                'user_id' => $userId,
                'room_id' => $validated['room_id'],
                'room_type_id' => $validated['room_type_id'],
                'check_in' => $validated['check_in'],
                'check_out' => $validated['check_out'],
                'guests_count' => $validated['guests_count'],
                'status' => $validated['status'],
                'total_amount' => $totals['total_amount'],
                'subtotal' => $totals['subtotal'],
                'service_charge_percentage' => $totals['service_charge_percentage'],
                'service_charge_amount' => $totals['service_charge_amount'],
                'vat_percentage' => $totals['vat_percentage'],
                'vat_amount' => $totals['vat_amount'],
                'deposit_percentage' => $validated['deposit_percentage'] ?? $booking->deposit_percentage,
                'deposit_amount' => $deposit,
                'due_amount' => $due,
                'coupon_id' => $validated['coupon_id'] ?? null,
                'discount_amount' => $totals['discount_amount'],
                'special_requests' => $validated['special_requests'] ?? null,
            ]);

            $booking->facilities()->sync(
                collect($facilities['lines'])->mapWithKeys(fn($line) => [
                    $line['facility_id'] => [
                        'quantity' => $line['quantity'],
                        'unit_price' => $line['unit_price'],
                        'subtotal' => $line['subtotal'],
                    ],
                ])->toArray()
            );

            return $booking;
        });

        return response()->json([
            'message' => "Booking {$booking->booking_reference} updated successfully.",
            'booking' => $booking->load(['user', 'room', 'roomType', 'facilities']),
        ]);
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Booking::ALLOWED_TRANSITIONS))],
        ]);

        $allowed = Booking::ALLOWED_TRANSITIONS[$booking->status] ?? [];

        if (!in_array($data['status'], $allowed)) {
            return response()->json(['message' => "Cannot move booking from '{$booking->status}' to '{$data['status']}'."], 422);
        }

        $booking->update(['status' => $data['status']]);

        if ($data['status'] === 'checked_in' && $booking->room) {
            $booking->room->update(['status' => 'occupied']);
        } elseif ($data['status'] === 'checked_out' && $booking->room) {
            $booking->room->update(['status' => 'dirty']);
        }

        return response()->json([
            'message' => "Status updated to {$data['status']}.",
            'status' => $booking->status,
        ]);
    }

    public function destroy(Booking $booking)
    {
        if (!in_array($booking->status, ['pending', 'cancelled'])) {
            return response()->json(['message' => 'Only pending or cancelled bookings can be deleted.'], 422);
        }

        if ($booking->coupon_id) {
            Coupon::where('id', $booking->coupon_id)->where('used_count', '>', 0)->decrement('used_count');
        }

        $booking->delete();

        return response()->json(['message' => 'Booking deleted successfully.']);
    }

    // ── Helpers ──────────────────────────────────────────────────

   protected function validateBookingRequest(Request $request, ?int $bookingId = null): array
{
    return $request->validate([
        'customer_mode' => 'required|in:existing,new',
        'user_id' => 'required_if:customer_mode,existing|nullable|exists:users,id',
        'new_customer.name' => 'required_if:customer_mode,new|nullable|string|max:255',
        'new_customer.email' => 'required_if:customer_mode,new|nullable|email|unique:users,email',
        'new_customer.phone' => 'required_if:customer_mode,new|nullable|string|max:20',
        'new_customer.nid_passport_number' => 'nullable|string|max:50',
        'room_type_id' => 'required|exists:room_types,id',
        'room_id' => 'required|exists:rooms,id',
        'check_in' => 'required|date',
        'check_out' => 'required|date|after:check_in',
        'guests_count' => 'required|integer|min:1',
        'status' => 'required|in:pending,confirmed',
        'coupon_id' => 'nullable|exists:coupons,id',
        'discount_amount' => 'nullable|numeric|min:0',
        'deposit_percentage' => 'nullable|numeric|min:0|max:100',
        'deposit_amount' => 'nullable|numeric|min:0',
        'special_requests' => 'nullable|string',
        'facilities' => 'nullable|array',
        'facilities.*.facility_id' => 'required_with:facilities|exists:facilities,id',
        'facilities.*.quantity' => 'required_with:facilities|integer|min:1|max:20',
    ]);
}


    protected function resolveFacilities(array $validated): array
    {
        if (empty($validated['facilities'])) {
            return ['subtotal' => 0.0, 'lines' => []];
        }

        $facilityIds = collect($validated['facilities'])->pluck('facility_id');
        $facilities = \App\Models\Facility::whereIn('id', $facilityIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        $subtotal = 0.0;
        $lines = [];

        foreach ($validated['facilities'] as $item) {
            $facility = $facilities->get($item['facility_id']);

            if (!$facility) {
                continue; // inactive or deleted since page load — silently skip
            }

            if ($facility->pricing_type !== 'fixed') {
                throw ValidationException::withMessages([
                    'facilities' => "\"{$facility->name}\" cannot be added to a booking (not a fixed-price facility).",
                ]);
            }

            $lineSubtotal = round((float) $facility->price * $item['quantity'], 2);

            $lines[] = [
                'facility_id' => $facility->id,
                'quantity' => $item['quantity'],
                'unit_price' => $facility->price,
                'subtotal' => $lineSubtotal,
            ];

            $subtotal += $lineSubtotal;
        }

        return ['subtotal' => round($subtotal, 2), 'lines' => $lines];
    }


    protected function resolveCustomer(array $validated, ?int $existingUserId = null): int
    {
        if ($validated['customer_mode'] === 'existing') {
            return $validated['user_id'];
        }

        $new = $validated['new_customer'];

        $user = User::create([
            'name' => $new['name'],
            'email' => $new['email'],
            'phone' => $new['phone'],
            'nid_passport_number' => $new['nid_passport_number'] ?? null,
            'role' => 'customer',
            'password' => Hash::make(Str::password(14)),
            'is_active' => true,
        ]);

        return $user->id;
    }

    protected function assertRoomStillAvailable(int $roomId, int $roomTypeId, string $checkIn, string $checkOut, ?int $excludeBookingId = null): void
    {
        $conflict = Booking::where('room_id', $roomId)
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->where('check_in', '<', $checkOut)
            ->where('check_out', '>', $checkIn)
            ->when($excludeBookingId, fn ($q) => $q->where('id', '!=', $excludeBookingId))
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'room_id' => 'This room was just booked for an overlapping date range. Please pick another room.',
            ]);
        }
    }

    protected function computeFinancials(float $roomTotal, float $facilitiesSubtotal, array $validated): array
    {
        $subtotal = $roomTotal + $facilitiesSubtotal;

        $discount = 0.0;
        if (!empty($validated['coupon_id'])) {
            $coupon = Coupon::find($validated['coupon_id']);
            if ($coupon) {
                $result = $this->coupons->validateAndCalculate($coupon->code, $subtotal);
                $discount = $result['discount_amount'];
            }
        } else {
            $discount = (float) ($validated['discount_amount'] ?? 0);
        }

        $netSubtotal = max(0, $subtotal - $discount);

        $serviceChargePct = (float) config('hotel.service_charge_percentage', 0);
        $vatPct = (float) config('hotel.vat_percentage', 0);
        $vatOnServiceCharge = (bool) config('hotel.vat_applies_to_service_charge', true);

        $serviceCharge = round($netSubtotal * ($serviceChargePct / 100), 2);

        $vatBase = $vatOnServiceCharge ? ($netSubtotal + $serviceCharge) : $netSubtotal;
        $vat = round($vatBase * ($vatPct / 100), 2);

        $totalAmount = round($netSubtotal + $serviceCharge + $vat, 2);

        $depositPct = (float) ($validated['deposit_percentage'] ?? config('hotel.deposit_percentage', 20));

        $depositAmount = array_key_exists('deposit_amount', $validated) && $validated['deposit_amount'] !== null
            ? (float) $validated['deposit_amount']
            : round($totalAmount * ($depositPct / 100), 2);

        $due = max(0, $totalAmount - $depositAmount);

        return [
            $depositAmount,
            $due,
            [
                'room_total' => $roomTotal,
                'facilities_subtotal' => $facilitiesSubtotal,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'service_charge_percentage' => $serviceChargePct,
                'service_charge_amount' => $serviceCharge,
                'vat_percentage' => $vatPct,
                'vat_amount' => $vat,
                'total_amount' => $totalAmount,
            ]
        ];
    }

    protected function generateReference(): string
    {
        do {
            $ref = 'BK' . now()->format('Ymd') . strtoupper(Str::random(4));
        } while (Booking::where('booking_reference', $ref)->exists());

        return $ref;
    }
}