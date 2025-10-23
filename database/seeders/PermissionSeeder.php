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

            // User & Role Management
            ['name' => 'view-user', 'display_name' => 'Lihat Pengguna'],
            ['name' => 'assign-role', 'display_name' => 'Tetapkan Role & Akses Perusahaan'], // Ubah display name agar lebih jelas
            ['name' => 'manage-roles', 'display_name' => 'Kelola Roles & Permissions'],

            // Master Data
            ['name' => 'manage-master-data', 'display_name' => 'Kelola Master Data'],

            // --- TAMBAHKAN PERMISSION LAPORAN ---
            ['name' => 'reports-view-inventory', 'display_name' => 'Lihat Lap. Inventaris'],
            ['name' => 'reports-export-inventory', 'display_name' => 'Ekspor Lap. Inventaris'],
            ['name' => 'reports-view-tracking', 'display_name' => 'Lihat Lap. Pelacakan'],
            ['name' => 'reports-export-tracking', 'display_name' => 'Ekspor Lap. Pelacakan'],
            // --- AKHIR PENAMBAHAN ---

        ];

        foreach ($permissions as $permission) {
            // Gunakan updateOrCreate untuk menghindari duplikasi dan memudahkan update display_name
            Permission::updateOrCreate(
                ['name' => $permission['name']], // Kolom unik untuk mencari
                $permission // Data lengkap untuk create atau update
            );
        }
    }
}
