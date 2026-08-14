<?php

$b = App\Models\Booking::with(['room', 'roomType', 'facilities', 'payments'])->latest()->first();

dump([
    'ref' => $b->booking_reference,
    'status' => $b->status,
    'room' => $b->room->room_number,
    'room_type' => $b->roomType->name,
    'check_in' => $b->check_in->toDateString(),
    'check_out' => $b->check_out->toDateString(),
    'total_amount' => $b->total_amount,
    'deposit_amount' => $b->deposit_amount,
    'due_amount' => $b->due_amount,
    'due_math_ok' => bccomp($b->due_amount, $b->total_amount - $b->deposit_amount, 2) === 0,
    'facilities' => $b->facilities->map(fn ($f) => [$f->name => $f->pivot->subtotal]),
    'payment_status' => $b->payments->first()?->status,
    'room_double_booked' => App\Models\Booking::where('room_id', $b->room_id)
        ->where('id', '!=', $b->id)
        ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
        ->where('check_in', '<', $b->check_out)
        ->where('check_out', '>', $b->check_in)
        ->exists(),
]);