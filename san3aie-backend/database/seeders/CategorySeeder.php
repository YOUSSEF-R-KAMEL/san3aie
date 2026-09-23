<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'سباك', 'icon' => '🔧'],
            ['name' => 'كهربائي', 'icon' => '⚡'],
            ['name' => 'نقاش', 'icon' => '🎨'],
            ['name' => 'نجار', 'icon' => '🪚'],
            ['name' => 'حداد', 'icon' => '🔨'],
            ['name' => 'مبلط', 'icon' => '🧱'],
            ['name' => 'فني تكييف', 'icon' => '❄️'],
            ['name' => 'ألوميتال', 'icon' => '🏠'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
