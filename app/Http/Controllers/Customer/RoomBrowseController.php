<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use App\Services\AvailabilityService;
use App\Services\RoomPricingService;
use Illuminate\Http\Request;

class RoomBrowseController extends Controller
{
    public function __construct(
        protected RoomPricingService $pricing,
        protected AvailabilityService $availability
    ) {}

    public function index(Request $request)
    {
        $data = $request->validate([
            'check_in' => 'nullable|date|after_or_equal:today',
            'check_out' => 'nullable|date|after:check_in',
            'adults' => 'nullable|integer|min:1',
            'children' => 'nullable|integer|min:0',
        ]);

        $checkIn = $data['check_in'] ?? now()->toDateString();
        $checkOut = $data['check_out'] ?? now()->addDay()->toDateString();
        $adults = $data['adults'] ?? 1;
        $children = $data['children'] ?? 0;
        $guests = $adults + $children;

        $roomTypes = RoomType::where('is_active', true)->orderBy('name')->get();

        $results = $roomTypes->map(function ($roomType) use ($checkIn, $checkOut, $guests) {
            $availableCount = $this->availability->countAvailable($roomType->id, $checkIn, $checkOut);
            $fits = $guests <= $roomType->max_capacity;
            $pricing = ($availableCount > 0 && $fits)
                ? $this->pricing->calculateForStay($roomType, $checkIn, $checkOut, $guests)
                : null;

            return [
                'room_type' => $roomType,
                'available_rooms' => $availableCount,
                'fits_guests' => $fits,
                'total_price' => $pricing['total'] ?? null,
                'nights' => $pricing['nights'] ?? null,
            ];
        });

        return view('customer.rooms.index', [
            'results' => $results,
            'search' => compact('checkIn', 'checkOut', 'adults', 'children'),
        ]);
    }

    public function show(Request $request, RoomType $roomType)
    {
        abort_unless($roomType->is_active, 404);

        $checkIn = $request->query('check_in', now()->toDateString());
        $checkOut = $request->query('check_out', now()->addDay()->toDateString());
        $adults = (int) $request->query('adults', 1);
        $children = (int) $request->query('children', 0);
        $guests = max($adults + $children, 1);

        $availableCount = $this->availability->countAvailable($roomType->id, $checkIn, $checkOut);
        $pricing = $availableCount > 0
            ? $this->pricing->calculateForStay($roomType, $checkIn, $checkOut, $guests)
            : null;

        return view('customer.rooms.show', [
            'roomType' => $roomType,
            'availableCount' => $availableCount,
            'pricing' => $pricing,
            'search' => compact('checkIn', 'checkOut', 'adults', 'children'),
        ]);
    }
}