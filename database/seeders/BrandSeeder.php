<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Brand;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            ['name' => 'Johnson & Johnson', 'description' => 'Global healthcare products manufacturer', 'slug' => 'johnson-and-johnson'],
            ['name' => 'Medtronic', 'description' => 'Medical device company', 'slug' => 'medtronic'],
            ['name' => 'Pfizer', 'description' => 'Pharmaceutical corporation', 'slug' => 'pfizer'],
            ['name' => 'GE Healthcare', 'description' => 'Medical technology and solutions provider', 'slug' => 'ge-healthcare'],
            ['name' => 'Siemens Healthineers', 'description' => 'Medical technology company', 'slug' => 'siemens-healthineers'],
            ['name' => 'Abbott Laboratories', 'description' => 'Healthcare products manufacturer', 'slug' => 'abbott-laboratories'],
            ['name' => 'Philips Healthcare', 'description' => 'Health technology company', 'slug' => 'philips-healthcare'],
            ['name' => 'Roche', 'description' => 'Healthcare research and diagnostics', 'slug' => 'roche'],
            ['name' => 'Stryker', 'description' => 'Medical technologies corporation', 'slug' => 'stryker'],
            ['name' => 'Boston Scientific', 'description' => 'Medical device manufacturer', 'slug' => 'boston-scientific']
        ];

        foreach ($brands as $brand) {
            Brand::create($brand);
        }
    }
}
