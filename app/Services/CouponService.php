<?php

namespace App\Services;

use App\Models\Coupon;
use Illuminate\Validation\ValidationException;

class CouponService
{
    /**
     * Validate a coupon code against an amount and return the computed discount.
     *
     * @return array{coupon: Coupon, discount_amount: float}
     */
    public function validateAndCalculate(string $code, float $amount): array
    {
        $coupon = Coupon::where('code', strtoupper(trim($code)))->first();

        if (!$coupon || !$coupon->is_active) {
            throw ValidationException::withMessages(['coupon_code' => 'Invalid or inactive coupon code.']);
        }

        $today = now()->toDateString();

        if ($coupon->valid_from && $today < $coupon->valid_from->toDateString()) {
            throw ValidationException::withMessages(['coupon_code' => 'This coupon is not active yet.']);
        }

        if ($coupon->valid_until && $today > $coupon->valid_until->toDateString()) {
            throw ValidationException::withMessages(['coupon_code' => 'This coupon has expired.']);
        }

        if (!is_null($coupon->max_uses) && $coupon->used_count >= $coupon->max_uses) {
            throw ValidationException::withMessages(['coupon_code' => 'This coupon has reached its usage limit.']);
        }

        if (!is_null($coupon->min_amount) && $amount < $coupon->min_amount) {
            throw ValidationException::withMessages(['coupon_code' => "Minimum booking amount for this coupon is ৳" . number_format($coupon->min_amount, 2) . "."]);
        }

        $discount = $coupon->type === 'percentage'
            ? round($amount * ((float) $coupon->value / 100), 2)
            : (float) $coupon->value;

        $discount = min($discount, $amount);

        return ['coupon' => $coupon, 'discount_amount' => $discount];
    }
}