<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
class User extends Authenticatable
{
   
    use HasFactory;
    protected $guarded = ['id'];

    /**
     * Relasi many-to-many ke model Role.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    // ... (fungsi hasRole dan hasPermissionTo) ...
    
    // --- TAMBAHKAN FUNGSI INI ---
    /**
     * Relasi many-to-many ke Company (untuk hak akses data).
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_user');
    }
    // --- AKHIR PENAMBAHAN ---

    /**
     * Helper untuk mengecek apakah user memiliki role tertentu.
     * @param string $roleName
     * @return bool
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Helper untuk mengecek apakah user memiliki permission tertentu melalui rolenya.
     * @param string $permissionName
     * @return bool
     */
    public function hasPermissionTo(string $permissionName): bool
    {
        return $this->roles()->whereHas('permissions', function ($query) use ($permissionName) {
            $query->where('name', $permissionName);
        })->exists();
    }
}