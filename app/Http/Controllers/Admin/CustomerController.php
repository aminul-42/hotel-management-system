<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = User::where('role', 'customer')
            ->withCount('bookings')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.customers.index', compact('customers'));
    }

    public function show(User $customer)
    {
        abort_if($customer->role !== 'customer', 404);

        $bookings = Booking::where('user_id', $customer->id)
            ->with('room.roomType')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'customer' => $customer,
            'bookings' => $bookings->map(fn ($b) => [
                'booking_reference' => $b->booking_reference,
                'room' => $b->room?->room_number,
                'room_type' => $b->room?->roomType?->name,
                'check_in' => $b->check_in?->format('d M Y'),
                'check_out' => $b->check_out?->format('d M Y'),
                'status' => $b->status,
                'total_amount' => $b->total_amount,
                'created_at' => $b->created_at->format('d M Y'),
            ]),
        ]);
    }

    public function toggleActive(User $customer)
    {
        abort_if($customer->role !== 'customer', 404);

        $customer->update(['is_active' => ! $customer->is_active]);

        return response()->json([
            'message' => $customer->is_active ? 'Customer unblocked.' : 'Customer blocked.',
            'is_active' => $customer->is_active,
        ]);
    }
}