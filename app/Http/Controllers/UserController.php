<?php
// File: app/Http/Controllers/UserController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Menampilkan daftar semua pengguna.
     */
    public function index()
    {
        // Ambil semua pengguna kecuali admin yang sedang login
        $users = User::where('id', '!=', auth()->id())->get();
        return view('users.index', compact('users'));
    }

    /**
     * Memperbarui role seorang pengguna.
     */
    public function updateRole(Request $request, User $user)
    {
        // Validasi input agar role yang dimasukkan harus salah satu dari pilihan yang ada
        $validated = $request->validate([
            'role' => ['required', Rule::in(['admin', 'viewer', 'user'])],
        ]);

        // Update role pengguna dan simpan
        $user->role = $validated['role'];
        $user->save();

        return redirect()->route('users.index')->with('success', 'Role untuk pengguna ' . $user->nama_pengguna . ' berhasil diperbarui.');
    }
}