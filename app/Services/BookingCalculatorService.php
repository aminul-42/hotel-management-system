<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Coupon;
use App\Models\Facility;
use App\Models\RoomType;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Mirrors Admin\BookingController's private pricing/facility/financial logic
 * so the numbers a customer sees always match what gets persisted. Kept as
 * its own service (rather than reusing the admin controller) so the admin
 * module stays untouched.
 */
class BookingCalculatorService
{
    public function __construct(
        protected RoomPricingService $pricing,
        protected CouponService $coupons,
        protected AvailabilityService $availability
    ) {}

    public function computeBreakdown(array $payload): array
    {
        $roomType = RoomType::findOrFail($payload['room_type_id']);
        $roomPricing = $this->pricing->calculateForStay(
            $roomType, $payload['check_in'], $payload['check_out'], $payload['guests_count']
        );
        $facilities = $this->resolveFacilities($payload['facilities'] ?? []);

        $subtotal = $roomPricing['total'] + $facilities['subtotal'];

        $discount = 0.0;
        $couponId = null;
        if (!empty($payload['coupon_code'])) {
            $result = $this->coupons->validateAndCalculate($payload['coupon_code'], $subtotal);
            $discount = $result['discount_amount'];
            $couponId = $result['coupon']->id;
        }

        $netSubtotal = max(0, $subtotal - $discount);

        $serviceChargePct = (float) config('hotel.service_charge_percentage', 0);
        $vatPct = (float) config('hotel.vat_percentage', 0);
        $vatOnServiceCharge = (bool) config('hotel.vat_applies_to_service_charge', true);

        $serviceCharge = round($netSubtotal * ($serviceChargePct / 100), 2);
        $vatBase = $vatOnServiceCharge ? ($netSubtotal + $serviceCharge) : $netSubtotal;
        $vat = round($vatBase * ($vatPct / 100), 2);
        $totalAmount = round($netSubtotal + $serviceCharge + $vat, 2);

        $depositPct = (float) config('hotel.deposit_percentage', 20);
        $depositAmount = round($totalAmount * ($depositPct / 100), 2);
        $due = max(0, $totalAmount - $depositAmount);

        return [
            'roomType' => $roomType,
            'roomPricing' => $roomPricing,
            'facilities' => $facilities,
            'subtotal' => round($subtotal, 2),
            'discount' => $discount,
            'couponId' => $couponId,
            'netSubtotal' => round($netSubtotal, 2),
            'serviceChargePct' => $serviceChargePct,
            'serviceCharge' => $serviceCharge,
            'vatPct' => $vatPct,
            'vat' => $vat,
            'totalAmount' => $totalAmount,
            'depositPct' => $depositPct,
            'depositAmount' => $depositAmount,
            'due' => $due,
        ];
    }

    protected function resolveFacilities(array $items): array
    {
        if (empty($items)) {
            return ['subtotal' => 0.0, 'lines' => []];
        }

        $ids = collect($items)->pluck('facility_id');
        $facilities = Facility::whereIn('id', $ids)->where('is_active', true)->get()->keyBy('id');

        $subtotal = 0.0;
        $lines = [];

        foreach ($items as $item) {
            $facility = $facilities->get($item['facility_id']);
            if (!$facility) continue;

            if ($facility->pricing_type !== 'fixed') {
                throw ValidationException::withMessages([
                    'facilities' => "\"{$facility->name}\" is not bookable online.",
                ]);
            }

            $lineSubtotal = round((float) $facility->price * $item['quantity'], 2);
            $lines[] = [
                'facility_id' => $facility->id,
                'name' => $facility->name,
                'quantity' => $item['quantity'],
                'unit_price' => $facility->price,
                'subtotal' => $lineSubtotal,
            ];
            $subtotal += $lineSubtotal;
        }

        return ['subtotal' => round($subtotal, 2), 'lines' => $lines];
    }

    /**
     * Called ONLY after payment succeeds. Creates the real Booking row.
     */
    public function createBookingFromPayload(array $payload, float $paidAmount): Booking
    {
        $breakdown = $this->computeBreakdown($payload);
        $room = $this->availability->assignRoom($payload['room_type_id'], $payload['check_in'], $payload['check_out']);

        $booking = Booking::create([
            'booking_reference' => $this->generateReference(),
            'user_id' => $payload['user_id'],
            'room_id' => $room->id,
            'room_type_id' => $payload['room_type_id'],
            'check_in' => $payload['check_in'],
            'check_out' => $payload['check_out'],
            'guests_count' => $payload['guests_count'],
            'status' => 'confirmed', // deposit paid online => confirmed, not pending
            'total_amount' => $breakdown['totalAmount'],
            'subtotal' => $breakdown['subtotal'],
            'service_charge_percentage' => $breakdown['serviceChargePct'],
            'service_charge_amount' => $breakdown['serviceCharge'],
            'vat_percentage' => $breakdown['vatPct'],
            'vat_amount' => $breakdown['vat'],
            'deposit_percentage' => $breakdown['depositPct'],
            'deposit_amount' => $breakdown['depositAmount'],
            'due_amount' => $breakdown['due'],
            'coupon_id' => $breakdown['couponId'],
            'discount_amount' => $breakdown['discount'],
            'special_requests' => $payload['special_requests'] ?? null,
            'created_by' => null, // self-service online booking, no staff member created it
        ]);

        if (!empty($breakdown['facilities']['lines'])) {
            $booking->facilities()->attach(
                collect($breakdown['facilities']['lines'])->mapWithKeys(fn ($l) => [
                    $l['facility_id'] => [
                        'quantity' => $l['quantity'],
                        'unit_price' => $l['unit_price'],
                        'subtotal' => $l['subtotal'],
                    ],
                ])->toArray()
            );
        }

        if ($breakdown['couponId']) {
            Coupon::where('id', $breakdown['couponId'])->increment('used_count');
        }

        return $booking;
    }

    protected function generateReference(): string
    {
        do {
            $ref = 'BK' . now()->format('Ymd') . strtoupper(Str::random(4));
        } while (Booking::where('booking_reference', $ref)->exists());

        return $ref;
    }
}