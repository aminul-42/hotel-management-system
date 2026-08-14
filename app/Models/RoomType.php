<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'base_capacity',
        'max_capacity', 'amenities', 'images', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'amenities' => 'array',
            'images' => 'array',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function rates()
    {
        return $this->hasMany(RoomRate::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // Accessor — returns full URLs for all images
    public function getImageUrlsAttribute(): array
    {
        return collect($this->images ?? [])
            ->map(fn ($path) => asset('storage/' . $path))
            ->toArray();
    }
}