<?php

namespace App\Http\Controllers\Customer;

use App\Contracts\PaymentGatewayInterface;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\PendingTransaction;
use App\Services\BookingCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentGatewayInterface $gateway,
        protected BookingCalculatorService $calculator
    ) {}

    public function fakeCheckout(string $tranId)
    {
        if (!app()->environment('local')) abort(404);

        $txn = PendingTransaction::where('tran_id', $tranId)->where('status', 'initiated')->firstOrFail();

        return view('customer.payment.fake-checkout', compact('txn'));
    }

    public function fakeCallback(Request $request, string $tranId)
    {
        if (!app()->environment('local')) abort(404);

        $data = $request->validate(['status' => 'required|in:success,failed,cancelled']);

        $txn = PendingTransaction::where('tran_id', $tranId)->firstOrFail();

        // Idempotency: a second callback for an already-processed txn just redirects, no reprocessing.
        if ($txn->status !== 'initiated') {
            return $txn->status === 'success'
                ? redirect()->route('customer.payment.success', $txn->tran_id)
                : redirect()->route('customer.payment.failed', $txn->tran_id);
        }

        $result = $this->gateway->verify([
            'tran_id' => $tranId,
            'status' => $data['status'],
            'amount' => $txn->amount,
        ]);

        if (!$result['valid']) {
            abort(400, 'Invalid payment verification.');
        }

        if ($result['status'] !== 'success') {
            $txn->update(['status' => $result['status']]);
            return redirect()->route('customer.payment.failed', $txn->tran_id);
        }

        DB::transaction(function () use ($txn) {
            $booking = $this->calculator->createBookingFromPayload($txn->payload, (float) $txn->amount);

            Payment::create([
                'booking_id' => $booking->id,
                'tran_id' => $txn->tran_id,
                'val_id' => 'FAKE-VAL-' . strtoupper(Str::random(10)),
                'amount' => $txn->amount,
                'payment_type' => $txn->payment_type,
                'status' => 'success',
                'gateway_response' => ['simulated' => true, 'note' => 'Fake SSLCommerz gateway (dev only)'],
                'paid_at' => now(),
            ]);

            $txn->update(['status' => 'success', 'booking_id' => $booking->id]);

            session()->forget('pending_booking');
        });

        return redirect()->route('customer.payment.success', $txn->tran_id);
    }

    public function success(string $tranId)
    {
        $txn = PendingTransaction::where('tran_id', $tranId)->where('status', 'success')->firstOrFail();
        $booking = Booking::with(['room', 'roomType', 'facilities'])->findOrFail($txn->booking_id);

        return view('customer.booking.confirmation', compact('booking'));
    }

    public function failed(string $tranId)
    {
        $txn = PendingTransaction::where('tran_id', $tranId)->firstOrFail();

        return view('customer.booking.payment-failed', compact('txn'));
    }
}