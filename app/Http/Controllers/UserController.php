<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str; 

class UserController extends Controller
{
    public function index()
    {
        // Menampilkan pengguna dengan role admin, editor, dan viewer
        $users = User::whereIn('role', ['admin', 'editor', 'viewer'])
                     ->where('id', '!=', auth()->id())
                     ->latest()
                     ->paginate(15);
                     
        return view('users.index', compact('users'));
    }

    public function updateRole(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat mengubah role diri sendiri.');
        }

        if ($user->id === 1) {
            return back()->with('error', 'Role untuk Super Admin tidak dapat diubah.');
        }

        $validated = $request->validate([
            'role' => ['required', Rule::in(['admin', 'viewer', 'user', 'editor'])],
        ]);

        $user->role = $validated['role'];
        $user->save();

        return redirect()->route('users.index')->with('success', 'Role untuk pengguna ' . $user->nama_pengguna . ' berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if (auth()->id() !== 1) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk menghapus pengguna.');
        }
        if ($user->id === 1) {
            return back()->with('error', 'Super Admin tidak dapat dihapus.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }

    public function resetPassword(User $user)
    {
        if (auth()->id() !== 1) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk mereset password.');
        }
        if ($user->id === 1) {
            return back()->with('error', 'Password Super Admin hanya bisa diubah melalui halaman profil.');
        }

        $newPassword = Str::random(10);
        $user->password = Hash::make($newPassword);
        $user->save();

        return back()->with('success', 'Password untuk ' . $user->nama_pengguna . ' telah direset. Password baru: ' . $newPassword);
    }
}