<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\User;
use App\Models\Permission; // <-- 1. Tambahkan import ini

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // =====================================================================
        // PERBAIKAN: Tambahkan blok ini untuk mendefinisikan semua izin
        // =====================================================================
        try {
            // Ambil semua permission dari database
            $permissions = Permission::all();

            // Untuk setiap permission, definisikan sebuah Gate
            foreach ($permissions as $permission) {
                Gate::define($permission->name, function (User $user) use ($permission) {
                    // Gunakan metode hasPermissionTo() yang sudah ada di model User
                    return $user->hasPermissionTo($permission->name);
                });
            }
        } catch (\Exception $e) {
            // Ini untuk mencegah error saat migrasi jika tabel permissions belum ada
            // Anda bisa menambahkan log di sini jika perlu
        }
        // =====================================================================


        // Memberikan hak akses super ke role 'super-admin'
        // Kode ini akan berjalan SEBELUM pengecekan izin di atas.
        Gate::before(function (User $user, string $ability) {
            if ($user->hasRole('super-admin')) {
                return true; // Berikan semua izin
            }
        });
    }
}
