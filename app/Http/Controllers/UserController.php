<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::whereHas('roles')
                     ->where('id', '!=', auth()->id());

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nama_pengguna', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        $users = $query->with('roles')->latest()->paginate(15);
        $roles = Role::orderBy('display_name')->get();

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::orderBy('display_name')->get();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pengguna' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,id'],
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
    
    public function assignRoles(Request $request, User $user)
    {
        
        if ($user->id === 1) {
            return back()->with('error', 'Role untuk Super Admin tidak dapat diubah.');
        }

        $validated = $request->validate([
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $user->roles()->sync($request->input('roles', []));

        return redirect()->route('users.index')->with('success', 'Role untuk pengguna ' . $user->nama_pengguna . ' berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === 1) {
            return back()->with('error', 'Super Admin tidak dapat dihapus.');
        }
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }

    public function resetPassword(User $user)
    {
        if ($user->id === 1) {
            return back()->with('error', 'Password Super Admin hanya bisa diubah melalui halaman profil.');
        }
        $newPassword = Str::random(10);
        $user->password = Hash::make($newPassword);
        $user->save();
        return back()->with('success', 'Password untuk ' . $user->nama_pengguna . ' telah direset. Password baru: ' . $newPassword);
    }
}