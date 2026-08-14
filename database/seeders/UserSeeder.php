<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@hotel.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('123'),
                'role' => 'admin',
                'phone' => '01700000001',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'frontdesk@hotel.com'],
            [
                'name' => 'Front Desk User',
                'password' => Hash::make('123'),
                'role' => 'front_desk',
                'phone' => '01700000002',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'customer@hotel.com'],
            [
                'name' => 'Test Customer',
                'password' => Hash::make('123'),
                'role' => 'customer',
                'phone' => '01700000003',
                'is_active' => true,
            ]
        );
    }
}