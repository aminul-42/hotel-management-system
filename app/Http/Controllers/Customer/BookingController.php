<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = auth()->user()->bookings()->with(['room', 'roomType'])->latest()->paginate(10);

        return view('customer.booking.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        abort_unless($booking->user_id === auth()->id(), 403);

        $booking->load(['room', 'roomType', 'facilities', 'payments', 'coupon']);

        return view('customer.booking.show', compact('booking'));
    }
}