<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            'TABLET' => 'Solid dosage form intended for oral administration.',
            'AMPULE' => 'A sealed vial used to contain and preserve a sample, usually a liquid.',
            'BOX 100S' => 'Packaged box containing 100 units of a product.',
            'VIAL' => 'A small container used to hold liquid medicines.',
            'CAPSULE' => 'A solid dosage form in a gelatin shell for oral administration.',
            'BTLS' => 'Short for "Bottles", used for storing liquids or suspensions.',
            'SUPP' => 'Short for "Suppository", intended for rectal or vaginal administration.',
            'NEBULE' => 'Unit-dose liquid for nebulization.',
            'PRE-FILLED SYRINGE' => 'A syringe already filled with medication.',
            'DROPS' => 'Liquid medicine administered in drops.',
            'BAGS' => 'Flexible containers used for IV fluids or medications.',
            'AMPS' => 'Short for "Ampoules", similar to ampules.',
            'SYRINGE' => 'Device used to inject fluids into the body.',
            'CAP' => 'Short for "Caplet", a capsule-shaped tablet.',
            'EFFE TAB' => 'Effervescent tablet that dissolves in water.',
            'RESPULE' => 'Unit-dose container used in nebulizers.',
            'PCS' => 'Pieces - individual units of a product.',
            'SUSPENSION' => 'Liquid containing undissolved particles.',
            'BAG' => 'Flexible container, often used for IV or nutrition.',
            'B' => 'Generic abbreviation for bag or bottle.',
            'PATCH' => 'Transdermal patch for slow release of medicine.',
            'TAB' => 'Short for "Tablet", a compressed solid dose.',
            'NASAL SPRAY' => 'Spray medication delivered via the nose.',
            'TUBE' => 'Flexible container, often for creams or gels.',
            'SYRUP' => 'Sweet liquid medicine for oral use.',
            'POLYAMP' => 'Plastic ampoules, often for ophthalmic use.',
            'BSYRUP' => 'Bottle of syrup.',
            'OINTMENT' => 'Topical preparation for application to the skin.',
            'DURULES' => 'A type of extended-release capsule.',
            'BOTTLE' => 'Container typically used for liquids.',
            'TAB 100\'S' => 'Pack of 100 tablets.',
            'INHALER' => 'Device for administering medication by inhalation.',
            'CREAM' => 'Semi-solid topical preparation.',
            'NEEDLE' => 'Sharp medical device for injections or drawing blood.',
            'GALLON' => 'Large volume liquid container.',
            'BXS' => 'Boxes - general packaging unit.',
            'PACK' => 'Packaged set of products.',
            'ROLLS' => 'Rolled materials, like bandages.',
            'TEST KIT' => 'Diagnostic kit for testing various conditions.',
            'TUBES' => 'Cylindrical container for ointments or creams.',
            'DOZEN' => 'Pack of 12 units.',
            'PCD' => 'Could be interpreted as "per card" or packaging type.',
        ];

        foreach ($types as $name => $description) {
            DB::table('product_types')->updateOrInsert(
                ['name' => $name],
                ['description' => $description, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
