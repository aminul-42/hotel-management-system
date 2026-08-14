<?php

namespace App\Services;

use App\Models\RoomType;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class RoomPricingService
{
    /**
     * Calculate total price for a stay, with a per-night breakdown.
     *
     * @return array{total: float, nights: int, breakdown: array<int, array{date:string, rate:float, rate_name:string}>}
     */
    public function calculateForStay(RoomType $roomType, string|Carbon $checkIn, string|Carbon $checkOut, int $guestsCount = 1): array
    {
        $checkIn = Carbon::parse($checkIn)->startOfDay();
        $checkOut = Carbon::parse($checkOut)->startOfDay();

        $rates = $roomType->rates()->where('is_active', true)->get();

        $breakdown = [];
        $total = 0.0;

        $period = CarbonPeriod::create($checkIn, '1 day', $checkOut->copy()->subDay());

        foreach ($period as $date) {
            $nightly = $this->rateForDate($rates, $roomType, $date, $guestsCount);
            $breakdown[] = [
                'date' => $date->toDateString(),
                'rate' => $nightly['price'],
                'rate_name' => $nightly['name'],
            ];
            $total += $nightly['price'];
        }

        return [
            'total' => round($total, 2),
            'nights' => count($breakdown),
            'breakdown' => $breakdown,
        ];
    }

    protected function rateForDate($rates, RoomType $roomType, Carbon $date, int $guestsCount): array
    {
        $matching = $rates->filter(function ($rate) use ($date, $roomType, $guestsCount) {
            return match ($rate->rate_type) {
                'seasonal' => $rate->start_date && $rate->end_date
                    && $date->between($rate->start_date, $rate->end_date),
                'weekend' => !is_null($rate->day_of_week)
                    && (int) $rate->day_of_week === $date->dayOfWeek,
                'occupancy' => $guestsCount > $roomType->base_capacity,
                'base' => is_null($rate->start_date) && is_null($rate->day_of_week),
                default => false,
            };
        });

        if ($matching->isEmpty()) {
            $fallback = $rates->firstWhere('rate_type', 'base');
            return [
                'price' => $fallback ? (float) $fallback->price : 0.0,
                'name' => $fallback ? $fallback->name : 'No rate configured',
            ];
        }

        $winner = $matching->sortByDesc('priority')->first();

        return [
            'price' => (float) $winner->price,
            'name' => $winner->name,
        ];
    }
}