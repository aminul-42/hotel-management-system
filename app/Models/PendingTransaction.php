<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingTransaction extends Model
{
    protected $fillable = ['tran_id', 'payload', 'amount', 'payment_type', 'status', 'booking_id'];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'amount' => 'decimal:2',
        ];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}