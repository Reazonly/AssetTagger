<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class UserMasterController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'user');

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nama_pengguna', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('jabatan', 'like', "%{$searchTerm}%")
                  ->orWhere('departemen', 'like', "%{$searchTerm}%");
            });
        }

        $users = $query->latest()->paginate(15);
        return view('masters.users.index', compact('users'));
    }

    public function create()
    {
        return view('masters.users.create');
    }

   public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pengguna' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'departemen' => ['nullable', 'string', 'max:255'],
        ]);

        $createData = [
            'nama_pengguna' => $validated['nama_pengguna'],
            'jabatan' => $validated['jabatan'],
            'departemen' => $validated['departemen'],
            'role' => 'user',
        ];

        if (!empty($validated['email']) && !empty($validated['password'])) {
            $createData['email'] = $validated['email'];
            $createData['password'] = Hash::make($validated['password']);
        } else {
            $createData['email'] = Str::slug($validated['nama_pengguna']) . '-' . time() . '@placeholder.local';
            $createData['password'] = Hash::make(Str::random(32));
        }

        User::create($createData);

        return redirect()->route('master-data.users.index')->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        if ($user->role !== 'user') {
            return redirect()->route('master-data.users.index')->with('error', 'Anda tidak dapat mengedit pengguna dengan role selain "user" di halaman ini.');
        }
        $user->is_placeholder = Str::endsWith($user->email, '@placeholder.local');
        return view('masters.users.edit', compact('user'));
    }


    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'nama_pengguna' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'departemen' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $updateData = [
            'nama_pengguna' => $validated['nama_pengguna'],
            'jabatan' => $validated['jabatan'],
            'departemen' => $validated['departemen'],
        ];

        if (!empty($validated['email'])) {
            $updateData['email'] = $validated['email'];
        }
        
        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('master-data.users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }
    public function destroy(User $user)
    {
        if ($user->assets()->count() > 0) {
            return back()->with('error', 'Pengguna tidak dapat dihapus karena masih terhubung dengan data aset.');
        }

        $user->delete();
        return redirect()->route('master-data.users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}