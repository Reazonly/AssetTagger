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
            ['name' => 'view-dashboard', 'display_name' => 'Lihat Dashboard'],
            
            ['name' => 'view-asset', 'display_name' => 'Lihat Aset'],
            ['name' => 'create-asset', 'display_name' => 'Buat Aset'],
            ['name' => 'edit-asset', 'display_name' => 'Edit Aset'],
            ['name' => 'delete-asset', 'display_name' => 'Hapus Aset'],
            ['name' => 'import-asset', 'display_name' => 'Impor Aset'],
            ['name' => 'export-asset', 'display_name' => 'Ekspor Aset'],
            ['name' => 'print-asset', 'display_name' => 'Cetak Label Aset'],

            ['name' => 'view-user', 'display_name' => 'Lihat Pengguna'],
            ['name' => 'assign-role', 'display_name' => 'Tetapkan Role Pengguna'],
            
            ['name' => 'manage-master-data', 'display_name' => 'Kelola Master Data'],

            ['name' => 'manage-roles', 'display_name' => 'Kelola Roles & Permissions'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission['name']], $permission);
        }
    }
}