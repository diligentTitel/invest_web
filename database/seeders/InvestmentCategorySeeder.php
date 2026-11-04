<?php

namespace Database\Seeders;

use App\Models\InvestmentCategory;
use Illuminate\Database\Seeder;

class InvestmentCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Digital assets', 'slug' => 'digital-assets', 'description' => 'Digital asset opportunities where legally available.', 'sort_order' => 1],
            ['name' => 'Public markets', 'slug' => 'public-markets', 'description' => 'Verified publicly traded securities.', 'sort_order' => 2],
            ['name' => 'Business investments', 'slug' => 'business-investments', 'description' => 'Structured investments in operating businesses.', 'sort_order' => 3],
            ['name' => 'Real estate', 'slug' => 'real-estate', 'description' => 'Documented property investment structures.', 'sort_order' => 4],
            ['name' => 'Other opportunities', 'slug' => 'other-opportunities', 'description' => 'Other opportunities subject to legal review.', 'sort_order' => 5],
        ];

        foreach ($categories as $category) {
            InvestmentCategory::query()->updateOrCreate(
                ['slug' => $category['slug']],
                [...$category, 'is_visible' => true],
            );
        }
    }
}