<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Kick off a payment session.
     * $data = ['payload' => array, 'amount' => float, 'payment_type' => string]
     * Returns ['tran_id' => string, 'redirect_url' => string]
     */
    public function initiate(array $data): array;

    /**
     * Verify an incoming callback/IPN payload is authentic and normalize it.
     * Returns ['valid' => bool, 'tran_id' => string, 'status' => 'success'|'failed'|'cancelled', 'amount' => float|null]
     */
    public function verify(array $payload): array;
}