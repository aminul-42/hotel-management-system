<?php

return [
    'vat_percentage' => 15,
    'service_charge_percentage' => 5,
    'deposit_percentage' => 20,
    'free_cancellation_hours' => 48,
    'partial_refund_percentage' => 50,
    'vat_applies_to_service_charge' => true, // true = VAT on (subtotal - discount + service charge); false = VAT on (subtotal - discount) only
];