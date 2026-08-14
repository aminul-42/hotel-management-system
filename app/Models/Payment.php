<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 'tran_id', 'val_id', 'amount',
        'payment_type', 'status', 'gateway_response', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'gateway_response' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }
}