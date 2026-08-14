<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_type_id', 'room_number', 'floor', 'status', 'images', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // Accessor
    public function getImageUrlsAttribute(): array
    {
        return collect($this->images ?? [])
            ->map(fn ($path) => asset('storage/' . $path))
            ->toArray();
    }
}