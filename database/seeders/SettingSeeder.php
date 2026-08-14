<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['key' => 'app_name', 'value' => 'ADI International', 'type' => 'text', 'group' => 'general'],
            ['key' => 'app_logo', 'value' => null, 'type' => 'image', 'group' => 'general'],
            ['key' => 'app_favicon', 'value' => null, 'type' => 'image', 'group' => 'general'],
            ['key' => 'contact_email', 'value' => 'info@adihotel.test', 'type' => 'text', 'group' => 'general'],
            ['key' => 'contact_phone', 'value' => '+8801700000000', 'type' => 'text', 'group' => 'general'],

            // Branding
            ['key' => 'hero_banner_image', 'value' => null, 'type' => 'image', 'group' => 'branding'],
            ['key' => 'hero_tagline', 'value' => 'Experience Luxury in the Heart of the City', 'type' => 'text', 'group' => 'branding'],

            // Financial
            ['key' => 'currency', 'value' => 'BDT', 'type' => 'text', 'group' => 'financial'],
            ['key' => 'vat_percentage', 'value' => '15', 'type' => 'number', 'group' => 'financial'],
            ['key' => 'service_charge_percentage', 'value' => '5', 'type' => 'number', 'group' => 'financial'],
            ['key' => 'deposit_percentage', 'value' => '20', 'type' => 'number', 'group' => 'financial'],

            // Booking Policy
            ['key' => 'free_cancellation_hours', 'value' => '48', 'type' => 'number', 'group' => 'booking_policy'],
            ['key' => 'partial_refund_percentage', 'value' => '50', 'type' => 'number', 'group' => 'booking_policy'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}