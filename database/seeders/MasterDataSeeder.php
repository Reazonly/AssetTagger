<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Category;
use App\Models\Unit;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            ['name' => 'Jhonlin Group', 'code' => 'JG'],
            ['name' => 'Jhonlin Marine Trans', 'code' => 'JMT'],
            ['name' => 'Jhonlin Baratama', 'code' => 'JB'],
            ['name' => 'Jhonlin Agro Raya', 'code' => 'JAR'],
            ['name' => 'Jhonlin Migas Lestari', 'code' => 'JML'],
        ];
        foreach ($companies as $company) {
            Company::create($company);
        }

        $elektronik = Category::create(['name' => 'Elektronik', 'code' => 'ELEC', 'requires_merk' => true]);
        $kendaraan = Category::create(['name' => 'Kendaraan', 'code' => 'VEHI', 'requires_merk' => true]);
        $furniture = Category::create(['name' => 'Furniture', 'code' => 'FURN', 'requires_merk' => false]);
        $atk = Category::create(['name' => 'Peralatan Kantor', 'code' => 'OFFI', 'requires_merk' => false]);

        Unit::create(['name' => 'Unit', 'category_id' => $elektronik->id]);
        Unit::create(['name' => 'Pcs', 'category_id' => $elektronik->id]);
        
        Unit::create(['name' => 'Unit', 'category_id' => $kendaraan->id]);

        Unit::create(['name' => 'Buah', 'category_id' => $furniture->id]);
        Unit::create(['name' => 'Set', 'category_id' => $furniture->id]);

        Unit::create(['name' => 'Pcs', 'category_id' => $atk->id]);
        Unit::create(['name' => 'Box', 'category_id' => $atk->id]);
        Unit::create(['name' => 'Rim', 'category_id' => $atk->id]);
    }
}
