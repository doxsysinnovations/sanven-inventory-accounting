<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $units = [
            // Weight-based units
            [
                'name' => 'Milligram',
                'code' => 'MG',
                'description' => 'Standard unit for measuring medication weight in milligrams'
            ],
            [
                'name' => 'Gram',
                'code' => 'G',
                'description' => 'Standard unit for measuring medication weight in grams'
            ],
            [
                'name' => 'Microgram',
                'code' => 'MCG',
                'description' => 'Unit for measuring very small medication doses in micrograms'
            ],

            // Volume-based units
            [
                'name' => 'Milliliter',
                'code' => 'ML',
                'description' => 'Standard unit for measuring liquid medication volume in milliliters'
            ],
            [
                'name' => 'Liter',
                'code' => 'LITER',
                'description' => 'Unit for measuring larger liquid volumes in liters'
            ],

            // Concentration-based units
            [
                'name' => 'Milligram per Milliliter',
                'code' => 'MG/ML',
                'description' => 'Concentration measurement for medications in mg per ml'
            ],
            [
                'name' => 'Microgram per Milliliter',
                'code' => 'MCG/ML',
                'description' => 'Concentration measurement for potent medications in mcg per ml'
            ],
            [
                'name' => 'International Unit',
                'code' => 'IU',
                'description' => 'International unit for measuring biological activity of substances'
            ],
            [
                'name' => 'Milliequivalent',
                'code' => 'MEQ',
                'description' => 'Unit measuring chemical activity, often for electrolytes'
            ],

            // Form-based units
            [
                'name' => 'Tablet',
                'code' => 'TABLET',
                'description' => 'Solid dosage form, typically oral medication'
            ],
            [
                'name' => 'Capsule',
                'code' => 'CAPSULE',
                'description' => 'Gelatin container for oral medication'
            ],
            [
                'name' => 'Syrup',
                'code' => 'SYRUP',
                'description' => 'Liquid oral medication form'
            ],

            // Time-release units
            [
                'name' => 'Milligram per 24 Hours',
                'code' => 'MG/24HR',
                'description' => 'Extended release medication dosage over 24 hours'
            ],

            // Insulin and specialized units
            [
                'name' => 'Units',
                'code' => 'UNITS',
                'description' => 'Standard measurement for insulin and other biological medications'
            ],

            // Medical supplies and equipment
            [
                'name' => 'Piece',
                'code' => 'PIECE',
                'description' => 'Individual item or unit count for medical supplies'
            ],
            [
                'name' => 'Pack',
                'code' => 'PACK',
                'description' => 'Packaged set of medical items'
            ],
            [
                'name' => 'Box',
                'code' => 'BOX',
                'description' => 'Box containing multiple medical items'
            ],
            [
                'name' => 'Roll',
                'code' => 'ROLL',
                'description' => 'Roll format for bandages or tapes'
            ],
            [
                'name' => 'Sterile Unit',
                'code' => 'STERILE',
                'description' => 'Sterilized medical equipment or supplies'
            ],

            // Measurement units for supplies
            [
                'name' => 'Inch',
                'code' => 'INCH',
                'description' => 'Imperial unit for measuring medical equipment dimensions'
            ],
            [
                'name' => 'Gallon',
                'code' => 'GALLON',
                'description' => 'Large volume measurement for liquids'
            ],
            [
                'name' => 'Gram Weight',
                'code' => 'GRAMS',
                'description' => 'Weight measurement for medical supplies'
            ],

            // Specialized medical units
            [
                'name' => 'SafeSet',
                'code' => 'SAFESET',
                'description' => 'Safety needle system or safety device'
            ],
            [
                'name' => 'Ratio Mix',
                'code' => '70/30',
                'description' => 'Fixed ratio medication mixture'
            ],

            // Brand-specific units
            [
                'name' => 'Ramavit',
                'code' => 'RAMAVIT',
                'description' => 'Brand-specific medication or supplement'
            ],
            [
                'name' => 'Coldrex',
                'code' => 'COLDREX',
                'description' => 'Cold and flu medication brand'
            ],
            [
                'name' => 'Symdex',
                'code' => 'SYMDEX',
                'description' => 'Brand-specific medication formulation'
            ],
            [
                'name' => 'Tudor',
                'code' => 'TUDOR',
                'description' => 'Brand-specific medical product'
            ],

            // Bulk quantities
            [
                'name' => 'Bulk Balls',
                'code' => 'BALLS',
                'description' => 'Cotton balls or similar medical supplies in bulk'
            ],
            [
                'name' => 'Partners Pack',
                'code' => 'PARTNERS',
                'description' => 'Partnership or bundled medical products'
            ],

            // Dimensional units
            [
                'name' => 'Dimension',
                'code' => '28X24',
                'description' => 'Specific dimensional measurement for medical equipment'
            ],
            [
                'name' => 'Size 3x4 Inch',
                'code' => '3INX4IN',
                'description' => 'Standard dressing or pad size'
            ],
            [
                'name' => 'Tape Roll 1.25x5M',
                'code' => '1.25X5M',
                'description' => 'Medical tape with specific dimensions'
            ],
            [
                'name' => 'Tape Roll 2.50x5M',
                'code' => '2.50X5M',
                'description' => 'Wider medical tape with specific dimensions'
            ],
        ];

        foreach ($units as $unit) {
            Unit::create([
                'name' => $unit['name'],
                'code' => $unit['code'],
                'description' => $unit['description'],
            ]);
        }
    }
}
