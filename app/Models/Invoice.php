<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 'invoice_number', 'room_charge', 'extra_charges',
        'vat_amount', 'service_charge_amount', 'discount_amount',
        'deposit_paid', 'total_due', 'pdf_path', 'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'room_charge' => 'decimal:2',
            'extra_charges' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'service_charge_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'deposit_paid' => 'decimal:2',
            'total_due' => 'decimal:2',
            'generated_at' => 'datetime',
        ];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    // Accessor
    public function getPdfUrlAttribute(): ?string
    {
        return $this->pdf_path ? asset('storage/' . $this->pdf_path) : null;
    }
}