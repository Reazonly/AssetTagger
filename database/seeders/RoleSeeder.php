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
        
        
        $kepalaDivisiRole = Role::create(['name' => 'kepala-divisi', 'display_name' => 'Kepala Divisi']);
        
        $permissions = Permission::all(); 

        
        $superAdminRole->permissions()->sync($permissions->pluck('id'));
        $kepalaDivisiRole->permissions()->sync($permissions->pluck('id')); 

        $adminPermissions = $permissions->whereNotIn('name', ['manage-roles']);
        $adminRole->permissions()->sync($adminPermissions->pluck('id'));

        $editorPermissions = $permissions->whereIn('name', [
            'view-dashboard', 'view-asset', 'create-asset', 'edit-asset', 
            'delete-asset', 'import-asset', 'export-asset', 'print-asset'
        ]);
        $editorRole->permissions()->sync($editorPermissions->pluck('id'));

        $viewerPermissions = $permissions->whereIn('name', ['view-dashboard', 'view-asset']);
        $viewerRole->permissions()->sync($viewerPermissions->pluck('id'));
        
        $superAdminUser = User::find(1);
        if ($superAdminUser) {
            $superAdminUser->roles()->sync([$superAdminRole->id]);
        }
    }
}
