<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'nid_passport_number',
        'nid_passport_image',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function createdBookings()
    {
        return $this->hasMany(Booking::class, 'created_by');
    }

    // Role helpers
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isFrontDesk(): bool
    {
        return $this->role === 'front_desk';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    // Accessor
    public function getNidPassportImageUrlAttribute(): ?string
    {
        return $this->nid_passport_image ? asset('storage/' . $this->nid_passport_image) : null;
    }
}