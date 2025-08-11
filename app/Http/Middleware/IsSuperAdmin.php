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
        // Cek apakah pengguna sudah login dan memiliki ID = 1
        if (auth()->check() && auth()->id() === 1) {
            return $next($request);
        }

        // Jika bukan, kembalikan ke halaman sebelumnya dengan pesan error
        return back()->with('error', 'Anda tidak memiliki hak akses untuk tindakan ini.');
    }
}
