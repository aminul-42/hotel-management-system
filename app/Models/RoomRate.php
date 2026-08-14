<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_type_id', 'name', 'rate_type', 'price',
        'start_date', 'end_date', 'day_of_week', 'priority', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }
}