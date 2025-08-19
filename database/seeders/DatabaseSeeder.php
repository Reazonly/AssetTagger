<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            MasterDataSeeder::class,
            SubCategorySeeder::class,

            PermissionSeeder::class,
            RoleSeeder::class,

            UserSeeder::class,
        ]);
    }
}