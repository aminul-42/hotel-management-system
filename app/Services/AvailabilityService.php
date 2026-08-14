<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AvailabilityService
{
    protected function bookedRoomIds(int $roomTypeId, string $checkIn, string $checkOut, ?int $excludeBookingId = null): Collection
    {
        return Booking::where('room_type_id', $roomTypeId)
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->where('check_in', '<', $checkOut)
            ->where('check_out', '>', $checkIn)
            ->when($excludeBookingId, fn ($q) => $q->where('id', '!=', $excludeBookingId))
            ->pluck('room_id');
    }

    public function countAvailable(int $roomTypeId, string $checkIn, string $checkOut): int
    {
        return Room::where('room_type_id', $roomTypeId)
            ->where('is_active', true)
            ->whereNotIn('id', $this->bookedRoomIds($roomTypeId, $checkIn, $checkOut))
            ->count();
    }

    public function assignRoom(int $roomTypeId, string $checkIn, string $checkOut): Room
    {
        $room = Room::where('room_type_id', $roomTypeId)
            ->where('is_active', true)
            ->whereNotIn('id', $this->bookedRoomIds($roomTypeId, $checkIn, $checkOut))
            ->orderBy('room_number')
            ->first();

        if (!$room) {
            throw ValidationException::withMessages([
                'room_type_id' => 'Sorry, no rooms of this type are available for the selected dates anymore.',
            ]);
        }

        return $room;
    }
}