<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat Roles
        $superAdminRole = Role::create(['name' => 'super-admin', 'display_name' => 'Super Administrator']);
        $adminRole = Role::create(['name' => 'admin', 'display_name' => 'Administrator']);
        $editorRole = Role::create(['name' => 'editor', 'display_name' => 'Editor']);
        $viewerRole = Role::create(['name' => 'viewer', 'display_name' => 'Viewer']);
        
        // =====================================================================
        // TAMBAHKAN BLOK INI UNTUK MEMBUAT ROLE KEPALA DIVISI SECARA OTOMATIS
        // =====================================================================
        $kepalaDivisiRole = Role::create(['name' => 'kepala-divisi', 'display_name' => 'Kepala Divisi']);
        // =====================================================================

        // 2. Ambil semua permissions
        $permissions = Permission::all(); // Ambil semua, jangan di-keyBy dulu

        // 3. Tetapkan permissions ke roles
        // Super Admin dan Kepala Divisi mendapatkan semua izin
        $superAdminRole->permissions()->sync($permissions->pluck('id'));
        $kepalaDivisiRole->permissions()->sync($permissions->pluck('id')); // <-- BERIKAN SEMUA IZIN

        // Admin mendapatkan hampir semua izin, kecuali manajemen role
        $adminPermissions = $permissions->whereNotIn('name', ['manage-roles']);
        $adminRole->permissions()->sync($adminPermissions->pluck('id'));

        // Editor bisa melihat dashboard dan mengelola aset
        $editorPermissions = $permissions->whereIn('name', [
            'view-dashboard', 'view-asset', 'create-asset', 'edit-asset', 
            'delete-asset', 'import-asset', 'export-asset', 'print-asset'
        ]);
        $editorRole->permissions()->sync($editorPermissions->pluck('id'));

        // Viewer hanya bisa melihat dashboard dan data aset
        $viewerPermissions = $permissions->whereIn('name', ['view-dashboard', 'view-asset']);
        $viewerRole->permissions()->sync($viewerPermissions->pluck('id'));
        
        // 4. Tetapkan role ke user pertama (Super Admin)
        $superAdminUser = User::find(1);
        if ($superAdminUser) {
            $superAdminUser->roles()->sync([$superAdminRole->id]);
        }
    }
}
