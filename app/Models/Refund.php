<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 'payment_id', 'amount', 'reason',
        'status', 'gateway_refund_ref', 'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}