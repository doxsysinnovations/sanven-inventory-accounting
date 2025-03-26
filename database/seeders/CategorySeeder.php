<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'GENERIC INJECTABLES',
                'slug' => 'generic-injectables',
            ],
            [
                'name' => 'ORALS',
                'slug' => 'orals',
            ],
            [
                'name' => 'MULTINATIONAL-NATIONAL PRODUCTS',
                'slug' => 'multinational-national-products',
            ],
            [
                'name' => 'MEDICAL SUPPLIES',
                'slug' => 'medical-supplies',
            ],
        ];

        foreach($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
