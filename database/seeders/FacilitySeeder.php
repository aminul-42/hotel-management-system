<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $facilities = [
            [
                'name' => 'Laundry Service',
                'slug' => 'laundry-service',
                'description' => 'Same-day laundry and dry cleaning service.',
                'pricing_type' => 'on_request',
                'price' => null,
            ],
            [
                'name' => 'Event Hall',
                'slug' => 'event-hall',
                'description' => 'Spacious hall available for events and meetings.',
                'pricing_type' => 'fixed',
                'price' => 15000.00,
            ],
            [
                'name' => 'Airport Pickup',
                'slug' => 'airport-pickup',
                'description' => 'Convenient pickup and drop-off from the airport.',
                'pricing_type' => 'fixed',
                'price' => 800.00,
            ],
        ];

        foreach ($facilities as $index => $facility) {
            Facility::updateOrCreate(
                ['slug' => $facility['slug']],
                array_merge($facility, ['is_active' => true, 'sort_order' => $index + 1])
            );
        }
    }
}