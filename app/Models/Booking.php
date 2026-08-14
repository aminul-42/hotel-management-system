<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;


    // Add near the top of the class body, after `use HasFactory;`

    public const ALLOWED_TRANSITIONS = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['checked_in', 'cancelled', 'no_show'],
        'checked_in' => ['checked_out'],
        'checked_out' => [],
        'cancelled' => [],
        'no_show' => [],
    ];

    public function getNightsAttribute(): int
    {
        return $this->check_in->diffInDays($this->check_out);
    }

    protected $fillable = [
        'booking_reference',
        'user_id',
        'room_id',
        'room_type_id',
        'check_in',
        'check_out',
        'guests_count',
        'status',
        'total_amount',
        'deposit_percentage',
        'deposit_amount',
        'due_amount',
        'coupon_id',
        'discount_amount',
        'special_requests',
        'created_by',
        'subtotal', 
        'service_charge_percentage',
        'service_charge_amount', 
        'vat_percentage', 
        'vat_amount',
    ];

    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
            'total_amount' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'due_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    public function facilities()
{
    return $this->belongsToMany(Facility::class, 'booking_facilities')
        ->withPivot('quantity', 'unit_price', 'subtotal')
        ->withTimestamps();
}
}