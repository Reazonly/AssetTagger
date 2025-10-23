<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Company; // Pastikan use Company ada
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log; // Tambahkan Log
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Menampilkan daftar pengguna (Manajemen Pengguna).
     */
    public function index(Request $request)
    {
        // Eager load relasi 'roles' dan 'companies'
        $query = User::whereHas('roles')
                     ->with(['roles', 'companies']) 
                     ->where('id', '!=', auth()->id()); 

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nama_pengguna', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        $users = $query->latest()->paginate(15);
        
        // Ambil semua roles dan companies untuk data di Modal
        $roles = Role::orderBy('display_name')->get();
        $companies = Company::orderBy('name')->get(); 

        return view('users.index', compact('users', 'roles', 'companies')); 
    }

    /**
     * Menampilkan form untuk membuat pengguna baru.
     */
    public function create()
    {
        $roles = Role::orderBy('display_name')->get();
        $companies = Company::orderBy('name')->get(); 
        return view('users.create', compact('roles', 'companies')); 
    }

    /**
     * Menyimpan pengguna baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pengguna' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles' => ['required', 'array', 'min:1'], // Wajib ada minimal 1 role
            'roles.*' => ['exists:roles,id'],
            'companies' => ['nullable', 'array'], 
            'companies.*' => ['exists:companies,id'], 
        ]);

        $user = User::create([
            'nama_pengguna' => $validated['nama_pengguna'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        
        $user->roles()->sync($validated['roles']); 

        if (!empty($validated['companies'])) {
            $user->companies()->sync($validated['companies']);
        }

        return redirect()->route('users.index')->with('success', 'Pengguna login baru berhasil ditambahkan.');
    }
    
    // --- INI METHOD BARU YANG MENGGANTIKAN assignRoles & assignCompanies ---
    /**
     * Update Role dan Hak Akses Perusahaan untuk seorang pengguna.
     */
    public function updateAccess(Request $request, User $user)
    {
        // Jangan izinkan mengubah akses super admin
        if ($user->id === 1 || $user->hasRole('super-admin')) {
             return back()->with('error', 'Role dan hak akses perusahaan untuk Super Admin tidak dapat diubah.');
        }

        $validated = $request->validate([
            'roles' => ['required', 'array', 'min:1'], // Pastikan minimal 1 role dipilih
            'roles.*' => ['exists:roles,id'],
            'companies' => ['nullable', 'array'],
            'companies.*' => ['exists:companies,id'],
        ], [
            'roles.required' => 'Pengguna harus memiliki setidaknya satu role.',
            'roles.min' => 'Pengguna harus memiliki setidaknya satu role.',
        ]);

        // Sync Roles
        $user->roles()->sync($validated['roles']);

        // Sync Companies
        // Jika input companies tidak ada (array kosong atau null), detach semua company
        $user->companies()->sync($request->input('companies', [])); 

        return redirect()->route('users.index')->with('success', 'Akses untuk pengguna ' . $user->nama_pengguna . ' berhasil diperbarui.');
    }
    // --- AKHIR METHOD BARU ---

    /**
     * Hapus method assignRoles(Request $request, User $user)
     * Hapus method assignCompanies(Request $request, User $user)
     * (Method di atas sudah digantikan oleh updateAccess)
     */

    /**
     * Menghapus pengguna dari database.
     */
    public function destroy(User $user)
    {
        if ($user->id === 1 || $user->hasRole('super-admin')) { 
            return back()->with('error', 'Super Admin tidak dapat dihapus.');
        }
        
        try {
            // Hapus relasi pivot terlebih dahulu
            $user->roles()->detach(); 
            $user->companies()->detach();
            
            $user->delete();
            return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
        
        } catch (\Exception $e) {
            Log::error("Gagal menghapus User ID {$user->id}: " . $e->getMessage());
             return back()->with('error', 'Gagal menghapus pengguna. Periksa apakah pengguna masih memiliki relasi lain.');
        }
    }

    /**
     * Reset password pengguna.
     */
    public function resetPassword(User $user)
    {
        if ($user->id === 1 || $user->hasRole('super-admin')) { 
            return back()->with('error', 'Password Super Admin hanya bisa diubah melalui halaman profil.');
        }
        
        $newPassword = Str::random(10);
        $user->password = Hash::make($newPassword);
        $user->save();
        
        return back()->with('success', 'Password untuk '."'$user->nama_pengguna'".' telah direset. Password baru: ' . $newPassword);
    }
}