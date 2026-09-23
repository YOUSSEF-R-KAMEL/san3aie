<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            AreaSeeder::class,
            AdminSeeder::class,
            SubscriptionPlanSeeder::class,
        ]);
    }
}
