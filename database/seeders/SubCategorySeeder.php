<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\SubCategory;

class SubCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil ID dari setiap kategori utama
        $elektronik = Category::where('code', 'ELEC')->first();
        $kendaraan = Category::where('code', 'VEHI')->first();
        $furniture = Category::where('code', 'FURN')->first();
        $peralatanKantor = Category::where('code', 'OFFI')->first();

        // Data untuk Kategori Elektronik
        if ($elektronik) {
            $subCategories = [
                'Laptop', 'Desktop/PC', 'Monitor', 'Printer', 'Proyektor', 'Scanner',
                'UPS (Uninterruptible Power Supply)', 'Speaker Aktif', 'Peralatan Jaringan (Router, Switch)',
                'Lainnya (Elektronik)',
            ];
            foreach ($subCategories as $name) {
                SubCategory::create(['name' => $name, 'category_id' => $elektronik->id]);
            }
        }

        // Data untuk Kategori Kendaraan
        if ($kendaraan) {
            $subCategories = [
                'Mobil Penumpang', 'Truk/Pick-up', 'Motor', 'Alat Berat (Excavator, Dozer)', 'Bus', 'Lainnya (Kendaraan)',
            ];
            foreach ($subCategories as $name) {
                SubCategory::create(['name' => $name, 'category_id' => $kendaraan->id]);
            }
        }
        
        // Data untuk Kategori Furniture
        if ($furniture) {
            $subCategories = [
                'Meja (Kerja, Rapat)', 'Kursi (Kerja, Tamu)', 'Lemari/Kabinet Arsip', 'Rak Penyimpanan',
                'Sofa', 'Partisi Ruangan', 'Lainnya (Furniture)',
            ];
            foreach ($subCategories as $name) {
                SubCategory::create(['name' => $name, 'category_id' => $furniture->id]);
            }
        }

        // Data untuk Kategori Peralatan Kantor
        if ($peralatanKantor) {
            $subCategories = [
                'Mesin Penghancur Kertas', 'Telepon Kantor', 'Papan Tulis (Whiteboard)', 'Dispenser Air',
                'Brankas', 'Mesin Absensi', 'Lainnya (Peralatan Kantor)',
            ];
            foreach ($subCategories as $name) {
                SubCategory::create(['name' => $name, 'category_id' => $peralatanKantor->id]);
            }
        }
    }
}