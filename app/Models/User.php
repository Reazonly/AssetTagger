<?php

namespace App\Models;

// --- TAMBAHKAN USE STATEMENT INI ---
use Illuminate\Database\Eloquent\Factories\HasFactory; 
// --- AKHIR PENAMBAHAN ---

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
// Hapus use Spatie\Permission\Traits\HasRoles; // Anda tidak menggunakan package Spatie

class User extends Authenticatable
{
   
    use HasFactory; // Baris ini sekarang akan berfungsi
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Gunakan cast 'hashed' jika di Laravel 10+
    ];

    /**
     * Relasi many-to-many ke model Role.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    /**
     * Relasi many-to-many ke Company (untuk hak akses data).
     */
    public function companies(): BelongsToMany
    {
        // Pastikan nama tabel pivot dan foreign key benar
        return $this->belongsToMany(Company::class, 'company_user', 'user_id', 'company_id'); 
    }

    /**
     * Helper untuk mengecek apakah user memiliki role tertentu.
     * @param string $roleName
     * @return bool
     */
    public function hasRole(string $roleName): bool
    {
        // Eager load roles jika belum atau gunakan cache jika perlu optimasi
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Helper untuk mengecek apakah user memiliki permission tertentu melalui rolenya.
     * MODIFIKASI: Tambahkan bypass untuk Super Admin.
     * @param string $permissionName
     * @return bool
     */
    public function hasPermissionTo(string $permissionName): bool
    {
        // --- TAMBAHKAN PENGECEKAN INI ---
        // Jika user adalah super-admin, langsung berikan akses
        if ($this->hasRole('super-admin')) {
            return true; 
        }
        // --- AKHIR PENAMBAHAN ---

        // Jika bukan super-admin, cek permission melalui role seperti biasa
        // Pastikan relasi 'permissions' ada di model Role
        return $this->roles()->whereHas('permissions', function ($query) use ($permissionName) {
            $query->where('name', $permissionName);
        })->exists();
    }
}

