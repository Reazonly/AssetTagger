<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Dashboard
            ['name' => 'view-dashboard', 'display_name' => 'Lihat Dashboard'],

            // Asset Management
            ['name' => 'view-asset', 'display_name' => 'Lihat Aset'],
            ['name' => 'create-asset', 'display_name' => 'Buat Aset'],
            ['name' => 'edit-asset', 'display_name' => 'Edit Aset'],
            ['name' => 'delete-asset', 'display_name' => 'Hapus Aset'],
            ['name' => 'import-asset', 'display_name' => 'Impor Aset'],
            ['name' => 'export-asset', 'display_name' => 'Ekspor Aset'],
            ['name' => 'print-asset', 'display_name' => 'Cetak Label Aset'],

            // User Management
            ['name' => 'view-user', 'display_name' => 'Lihat Pengguna'],
            ['name' => 'assign-role', 'display_name' => 'Tetapkan Role Pengguna'],
             ['name' => 'manage-roles', 'display_name' => 'Kelola Roles & Permissions'], // Nama untuk grup role management

            // Master Data
            ['name' => 'manage-master-data', 'display_name' => 'Kelola Master Data'], // Nama untuk grup master data

            // --- PERMISSION LAPORAN ---
            ['name' => 'reports-view-inventory', 'display_name' => 'Lihat Lap. Inventaris'],
            ['name' => 'reports-export-inventory', 'display_name' => 'Ekspor Lap. Inventaris'],
            ['name' => 'reports-view-tracking', 'display_name' => 'Lihat Lap. Pelacakan'],
            ['name' => 'reports-export-tracking', 'display_name' => 'Ekspor Lap. Pelacakan'],
            // --- AKHIR PERMISSION LAPORAN ---
        ];

        foreach ($permissions as $permission) {
            // Gunakan updateOrCreate untuk menghindari error duplikasi jika seeder dijalankan lagi
            Permission::updateOrCreate(
                ['name' => $permission['name']], // Kunci untuk mencari
                $permission                     // Data untuk update atau create
            );
        }
    }
}

