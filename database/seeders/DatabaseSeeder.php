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
            // Panggil seeder untuk master data dasar terlebih dahulu
            MasterDataSeeder::class,
            SubCategorySeeder::class,

            // Panggil seeder untuk Roles & Permissions
            PermissionSeeder::class, // Membuat semua izin yang ada
            RoleSeeder::class,       // Membuat role dan melampirkan izin ke role tersebut

            // Panggil seeder untuk membuat user awal
            UserSeeder::class,
        ]);
    }
}