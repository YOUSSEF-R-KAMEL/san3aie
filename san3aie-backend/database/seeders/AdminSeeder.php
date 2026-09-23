<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            [
                'phone' => '01099999999',
            ],
            [
                'name' => 'Admin',
                'password' => Hash::make('Admin@123456'),
                'role' => 'admin',
                'area_id' => null,
                'category_id' => null,
                'is_available' => false,
                'is_blocked' => false,
            ]
        );
    }
}
