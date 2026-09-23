<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Premium شهري',
                'description' => 'اشتراك Premium لمدة شهر',
                'price' => 99,
                'duration_days' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Premium 3 شهور',
                'description' => 'اشتراك Premium لمدة 3 شهور',
                'price' => 249,
                'duration_days' => 90,
                'is_active' => true,
            ],
            [
                'name' => 'Premium سنوي',
                'description' => 'اشتراك Premium لمدة سنة',
                'price' => 799,
                'duration_days' => 365,
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['name' => $plan['name']],
                $plan
            );
        }
    }
}
