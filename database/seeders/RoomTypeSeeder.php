<?php

namespace Database\Seeders;

use App\Models\RoomType;
use Illuminate\Database\Seeder;

class RoomTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Standard Room',
                'slug' => 'standard-room',
                'description' => 'A cozy room with all essential amenities for a comfortable stay.',
                'base_capacity' => 2,
                'max_capacity' => 2,
                'amenities' => ['Free WiFi', 'Air Conditioning', 'TV', 'Wardrobe'],
                'images' => [],
                'is_active' => true,
            ],
            [
                'name' => 'Deluxe Room',
                'slug' => 'deluxe-room',
                'description' => 'Spacious room with premium furnishing and a city view.',
                'base_capacity' => 2,
                'max_capacity' => 3,
                'amenities' => ['Free WiFi', 'Air Conditioning', 'Mini Fridge', 'City View', 'TV'],
                'images' => [],
                'is_active' => true,
            ],
            [
                'name' => 'Executive Suite',
                'slug' => 'executive-suite',
                'description' => 'Our finest suite with separate living area and premium services.',
                'base_capacity' => 2,
                'max_capacity' => 4,
                'amenities' => ['Free WiFi', 'Air Conditioning', 'Living Area', 'Mini Bar', 'City View', 'TV'],
                'images' => [],
                'is_active' => true,
            ],
        ];

        foreach ($types as $type) {
            RoomType::updateOrCreate(['slug' => $type['slug']], $type);
        }
    }
}