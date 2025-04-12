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
            ['name' => 'Tablets', 'code' => 'TAB'],
            ['name' => 'Capsules', 'code' => 'CAP'],
            ['name' => 'Milliliters', 'code' => 'ML'],
            ['name' => 'Vials', 'code' => 'VL'],
            ['name' => 'Ampoules', 'code' => 'AMP'],
            ['name' => 'Pieces', 'code' => 'PCS'],
            ['name' => 'Boxes', 'code' => 'BOX'],
            ['name' => 'Packets', 'code' => 'PKT'],
            ['name' => 'Bottles', 'code' => 'BTL'],
            ['name' => 'Strips', 'code' => 'STR'],
            ['name' => 'Packs', 'code' => 'PCK'],
            ['name' => 'Rolls', 'code' => 'ROL'],
            ['name' => 'Sachets', 'code' => 'SCH'],
            ['name' => 'Tubes', 'code' => 'TUB'],
            ['name' => 'Pouches', 'code' => 'PCH'],
            ['name' => 'Doses', 'code' => 'DOS'],
            ['name' => 'Inhalers', 'code' => 'INH'],
            ['name' => 'Syringes', 'code' => 'SYR'],
            ['name' => 'Dressings', 'code' => 'DRS'],
            ['name' => 'Gauzes', 'code' => 'GZE'],
            ['name' => 'Bandages', 'code' => 'BND'],
            ['name' => 'Cups', 'code' => 'CUP'],
            ['name' => 'Pills', 'code' => 'PIL'],
            ['name' => 'Blisters', 'code' => 'BLS'],
            ['name' => 'Containers', 'code' => 'CNT'],
            ['name' => 'Cartridges', 'code' => 'CTG'],
            ['name' => 'Tissues', 'code' => 'TSU'],
            ['name' => 'Swabs', 'code' => 'SWB'],
            ['name' => 'Filters', 'code' => 'FLT'],
            ['name' => 'Cylinders', 'code' => 'CYL'],
            ['name' => 'Tanks', 'code' => 'TNK'],
            ['name' => 'Carriers', 'code' => 'CRR'],
            ['name' => 'Trays', 'code' => 'TRY'],
            ['name' => 'Carts', 'code' => 'CRT'],
            ['name' => 'Trucks', 'code' => 'TRK'],
            ['name' => 'Bags', 'code' => 'BAG'],
            ['name' => 'Jars', 'code' => 'JAR'],
            ['name' => 'Sprays', 'code' => 'SPR'],
            ['name' => 'Droppers', 'code' => 'DRP'],
            ['name' => 'Applicators', 'code' => 'APP'],
            ['name' => 'Suppositories', 'code' => 'SUP'],
            ['name' => 'Injectors', 'code' => 'INJ'],
            ['name' => 'Pens', 'code' => 'PEN']
        ];
        foreach ($units as $unit) {
            if (!Unit::where('name', $unit['name'])->exists()) {
                Unit::create($unit);
            }
        }
    }
}
