<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\PendingTransaction;
use Illuminate\Support\Str;

/**
 * Dev-only stand-in for SSLCommerz. Mimics the real round trip:
 * initiate() -> redirect to a gateway page -> gateway posts back to our
 * success/fail/cancel + IPN endpoints. Swap this for SslcommerzGateway
 * (implementing the same interface) once real sandbox credentials exist —
 * nothing in the controllers above this layer needs to change.
 */
class FakeSslcommerzGateway implements PaymentGatewayInterface
{
    public function initiate(array $data): array
    {
        $tranId = 'FAKE' . now()->format('YmdHis') . strtoupper(Str::random(6));

        PendingTransaction::create([
            'tran_id' => $tranId,
            'payload' => $data['payload'],
            'amount' => $data['amount'],
            'payment_type' => $data['payment_type'] ?? 'deposit',
            'status' => 'initiated',
        ]);

        return [
            'tran_id' => $tranId,
            'redirect_url' => route('customer.payment.fake.checkout', $tranId),
        ];
    }

    public function verify(array $payload): array
    {
        // Fake gateway trusts its own locally-generated callback outright —
        // a real gateway class would check a signature/hash here instead.
        return [
            'valid' => true,
            'tran_id' => $payload['tran_id'] ?? null,
            'status' => $payload['status'] ?? 'failed',
            'amount' => $payload['amount'] ?? null,
        ];
    }
}