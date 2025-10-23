<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Company; // Pastikan hanya ada satu use Company
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // --- AWAL PENYESUAIAN index() ---
        $query = User::whereHas('roles')
                     ->with(['roles', 'companies']) // Eager load companies
                     ->where('id', '!=', auth()->id()); // Jangan tampilkan user yg login

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nama_pengguna', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        $users = $query->latest()->paginate(15);
        $roles = Role::orderBy('display_name')->get();
        $companies = Company::orderBy('name')->get(); // Ambil semua company untuk modal

        // Kirim $companies ke view
        return view('users.index', compact('users', 'roles', 'companies')); 
        // --- AKHIR PENYESUAIAN index() ---
    }

    public function create()
    {
        $roles = Role::orderBy('display_name')->get();
        $companies = Company::orderBy('name')->get(); 
        return view('users.create', compact('roles', 'companies')); 
    }

    public function store(Request $request)
    {
        // --- AWAL PENYESUAIAN store() ---
        $validated = $request->validate([
            'nama_pengguna' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,id'],
            'companies' => ['nullable', 'array'], // Tambah validasi companies
            'companies.*' => ['exists:companies,id'], // Tambah validasi companies.*
        ]);
        // --- AKHIR PENYESUAIAN store() ---

        $user = User::create([
            'nama_pengguna' => $validated['nama_pengguna'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        
        $user->roles()->sync($validated['roles']); 

        // Sync companies jika ada
        if (!empty($validated['companies'])) {
            $user->companies()->sync($validated['companies']);
        }

        return redirect()->route('users.index')->with('success', 'Pengguna login baru berhasil ditambahkan.');
    }
    
    public function assignRoles(Request $request, User $user)
    {
        // Jangan izinkan mengubah role super admin
        if ($user->id === 1 || $user->hasRole('super-admin') ) { // Ditambahkan cek hasRole juga
            return back()->with('error', 'Role untuk Super Admin tidak dapat diubah.');
        }

        $validated = $request->validate([
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        // Pastikan minimal ada 1 role jika tidak dihapus semua
        // if (empty($request->input('roles', []))) {
        //     return back()->with('error', 'Pengguna harus memiliki setidaknya satu role.');
        // }


        $user->roles()->sync($request->input('roles', []));

        return redirect()->route('users.index')->with('success', 'Role untuk pengguna ' . $user->nama_pengguna . ' berhasil diperbarui.');
    }

    // --- TAMBAHKAN METHOD BARU INI ---
    public function assignCompanies(Request $request, User $user)
    {
        // Jangan izinkan mengubah akses super admin (karena dia bisa akses semua)
        if ($user->hasRole('super-admin')) {
             return back()->with('error', 'Hak akses perusahaan untuk Super Admin tidak dapat diubah (selalu bisa akses semua).');
        }

        $validated = $request->validate([
            'companies' => ['nullable', 'array'],
            'companies.*' => ['exists:companies,id'],
        ]);

        $user->companies()->sync($request->input('companies', []));

        return redirect()->route('users.index')->with('success', 'Hak akses perusahaan untuk ' . $user->nama_pengguna . ' berhasil diperbarui.');
    }
    // --- AKHIR METHOD BARU ---

    public function destroy(User $user)
    {
        if ($user->id === 1 || $user->hasRole('super-admin')) { // Ditambahkan cek hasRole
            return back()->with('error', 'Super Admin tidak dapat dihapus.');
        }
        // Tambahkan validasi lain jika user masih punya relasi, misal Asset
        // if ($user->assets()->count() > 0) { // Contoh jika ada relasi ke Asset
        //     return back()->with('error', 'Pengguna tidak dapat dihapus karena masih terhubung dengan data aset.');
        // }
        
        try {
            // Hapus relasi dulu sebelum menghapus user
            $user->roles()->detach(); 
            $user->companies()->detach();
            $user->delete();
            return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error("Gagal menghapus User ID {$user->id}: " . $e->getMessage());
             return back()->with('error', 'Gagal menghapus pengguna. Cek log untuk detail.');
        }
    }

    public function resetPassword(User $user)
    {
        if ($user->id === 1 || $user->hasRole('super-admin')) { // Ditambahkan cek hasRole
            return back()->with('error', 'Password Super Admin hanya bisa diubah melalui halaman profil.');
        }
        $newPassword = Str::random(10);
        $user->password = Hash::make($newPassword);
        $user->save();
        return back()->with('success', 'Password untuk ' . $user->nama_pengguna . ' telah direset. Password baru: ' . $newPassword);
    }
}
