<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // =====================================================================
        // PERBAIKAN: Mengganti pengecekan dari ID pengguna ke nama role.
        // Sekarang, setiap pengguna dengan role 'super-admin' akan diizinkan.
        // =====================================================================
        if (auth()->check() && auth()->user()->hasRole('super-admin')) {
            return $next($request);
        }

        // Jika tidak, kembalikan ke halaman sebelumnya dengan pesan error.
        return back()->with('error', 'Anda tidak memiliki hak akses untuk tindakan ini.');
    }
}
