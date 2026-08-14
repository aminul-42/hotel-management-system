<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'image',
        'pricing_type', 'price', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'price' => 'decimal:2',
        ];
    }

    // Accessor
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    // Accessor: human-readable price label for display
    public function getPriceLabelAttribute(): string
    {
        return match ($this->pricing_type) {
            'free' => 'Free',
            'fixed' => number_format($this->price, 2) . ' BDT',
            'on_request' => 'Contact Front Desk',
            default => '',
        };
    }


    public function bookings()
{
    return $this->belongsToMany(Booking::class, 'booking_facilities')
        ->withPivot('quantity', 'unit_price', 'subtotal')
        ->withTimestamps();
}
}