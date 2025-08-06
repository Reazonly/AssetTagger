<?php
// File: database/seeders/UserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'nama_pengguna' => 'Admin Jhonlin',
            'email' => 'admin@jhonlin.co',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'nama_pengguna' => 'Viewer Jhonlin',
            'email' => 'viewer@jhonlin.co',
            'password' => Hash::make('password'),
            'role' => 'viewer',
        ]);
    }
}