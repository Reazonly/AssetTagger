<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SubCategorySeeder::class, // Tambahkan seeder baru di sini
            MasterDataSeeder::class,
        ]);
    }
}