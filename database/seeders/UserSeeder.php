<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Cari role 'super-admin' yang sudah dibuat oleh RoleSeeder
        $superAdminRole = Role::where('name', 'super-admin')->first();

        // Buat user baru
        $user = User::create([
            'nama_pengguna' => 'Super Admin',
            'email' => 'admin@jhonlin.co',
            'password' => Hash::make('password'),
            'jabatan' => 'IT Department',
            'departemen' => 'Head Office',
        ]);

        // Lampirkan role 'super-admin' ke user tersebut
        if ($superAdminRole) {
            $user->roles()->attach($superAdminRole->id);
        }
    }
}