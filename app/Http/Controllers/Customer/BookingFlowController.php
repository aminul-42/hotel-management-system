<?php

namespace App\Http\Controllers\Customer;

use App\Contracts\PaymentGatewayInterface;
use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\RoomType;
use App\Services\AvailabilityService;
use App\Services\BookingCalculatorService;
use App\Services\CouponService;
use App\Services\RoomPricingService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BookingFlowController extends Controller
{
    public function __construct(
        protected RoomPricingService $pricing,
        protected CouponService $coupons,
        protected AvailabilityService $availability,
        protected BookingCalculatorService $calculator,
        protected PaymentGatewayInterface $gateway
    ) {}

    public function start(Request $request)
    {
        $data = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
        ]);

        $roomType = RoomType::findOrFail($data['room_type_id']);
        $guests = $data['adults'] + ($data['children'] ?? 0);

        if ($guests > $roomType->max_capacity) {
            return back()->withErrors([
                'adults' => "Max capacity for {$roomType->name} is {$roomType->max_capacity} guests.",
            ])->withInput();
        }

        if ($this->availability->countAvailable($roomType->id, $data['check_in'], $data['check_out']) < 1) {
            return back()->withErrors([
                'room_type_id' => 'Sorry, no rooms of this type are available for those dates.',
            ])->withInput();
        }

        session(['pending_booking' => [
            'room_type_id' => $roomType->id,
            'check_in' => $data['check_in'],
            'check_out' => $data['check_out'],
            'guests_count' => $guests,
            'facilities' => [],
            'coupon_code' => null,
            'special_requests' => null,
        ]]);

        return redirect()->route('customer.booking.customize');
    }

    public function customize()
    {
        $pending = $this->requirePendingBooking();
        $roomType = RoomType::findOrFail($pending['room_type_id']);
        $pricingBreakdown = $this->pricing->calculateForStay(
            $roomType, $pending['check_in'], $pending['check_out'], $pending['guests_count']
        );
        $facilities = Facility::where('is_active', true)->where('pricing_type', 'fixed')->orderBy('sort_order')->get();

        return view('customer.booking.customize', compact('pending', 'roomType', 'pricingBreakdown', 'facilities'));
    }


    public function applyCoupon(Request $request)
    {
        $pending = $this->requirePendingBooking();

        $data = $request->validate([
            'coupon_code' => 'nullable|string',
            'facilities' => 'nullable|array',
            'facilities.*.facility_id' => 'required_with:facilities|exists:facilities,id',
            'facilities.*.quantity' => 'required_with:facilities|integer|min:1|max:20',
        ]);

        $payload = $pending;
        $payload['facilities'] = $data['facilities'] ?? [];
        $payload['coupon_code'] = $data['coupon_code'] ?? null;

        try {
            $breakdown = $this->calculator->computeBreakdown($payload + ['user_id' => auth()->id()]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first() ?: 'That coupon code isn\'t valid.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => empty($payload['coupon_code']) ? 'Coupon removed.' : 'Coupon applied successfully.',
            'subtotal' => $breakdown['subtotal'],
            'discount' => $breakdown['discount'],
            'totalAmount' => $breakdown['totalAmount'],
            'depositAmount' => $breakdown['depositAmount'],
        ]);
    }

    public function saveCustomize(Request $request)
    {
        $pending = $this->requirePendingBooking();

        $data = $request->validate([
            'facilities' => 'nullable|array',
            'facilities.*.facility_id' => 'required_with:facilities|exists:facilities,id',
            'facilities.*.quantity' => 'required_with:facilities|integer|min:1|max:20',
            'coupon_code' => 'nullable|string',
            'special_requests' => 'nullable|string|max:1000',
        ]);

        $pending['facilities'] = $data['facilities'] ?? [];
        $pending['coupon_code'] = $data['coupon_code'] ?? null;
        $pending['special_requests'] = $data['special_requests'] ?? null;

        if (!empty($pending['coupon_code'])) {
            try {
                $breakdown = $this->calculator->computeBreakdown($pending + ['user_id' => null]);
            } catch (ValidationException $e) {
                return back()->withErrors($e->validator)->withInput();
            }
        }

        session(['pending_booking' => $pending]);

        return redirect()->route('customer.booking.review');
    }

    public function review()
    {
        $pending = $this->requirePendingBooking();
        $breakdown = $this->calculator->computeBreakdown($pending + ['user_id' => auth()->id()]);

        return view('customer.booking.review', compact('pending', 'breakdown'));
    }

    public function confirm(Request $request)
    {
        $pending = $this->requirePendingBooking();

        if ($this->availability->countAvailable($pending['room_type_id'], $pending['check_in'], $pending['check_out']) < 1) {
            return redirect()->route('customer.rooms.index')->withErrors([
                'room_type_id' => 'Sorry, this room type just sold out for your dates.',
            ]);
        }

        $payload = $pending + ['user_id' => auth()->id()];
        $breakdown = $this->calculator->computeBreakdown($payload);

        $init = $this->gateway->initiate([
            'payload' => $payload,
            'amount' => $breakdown['depositAmount'],
            'payment_type' => 'deposit',
        ]);

        return redirect($init['redirect_url']);
    }

    protected function requirePendingBooking(): array
    {
        $pending = session('pending_booking');

        if (!$pending) {
            abort(redirect()->route('customer.rooms.index')
                ->with('error', 'Your booking session expired. Please start again.'));
        }

        return $pending;
    }
}