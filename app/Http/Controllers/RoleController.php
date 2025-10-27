<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log; // Tambahkan Log jika belum ada

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('permissions')->latest()->paginate(15);
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        // Ambil semua permission, urutkan, lalu kelompokkan
        $permissions = Permission::orderBy('display_name')->get()->groupBy(function($item) {
            // Ambil bagian sebelum tanda hubung pertama sebagai nama grup
            $parts = explode('-', $item->name, 2); // Limit jadi 2 bagian
            return $parts[0]; // Gunakan bagian pertama (e.g., 'reports', 'view', 'manage')
        });

        // Debug: Lihat hasil grouping di log
        // Log::info('Grouped Permissions (Create): ', $permissions->toArray()); 

        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name|regex:/^[a-z0-9\-]+$/', // Hanya huruf kecil, angka, dan tanda hubung
            'display_name' => 'required|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ], [
            'name.regex' => 'Nama Role (Slug) hanya boleh berisi huruf kecil, angka, dan tanda hubung (-).',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'],
        ]);

        if (!empty($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return redirect()->route('roles.index')->with('success', 'Role baru berhasil ditambahkan.');
    }

    public function edit(Role $role)
    {
         // Logika grouping sama dengan create()
        $permissions = Permission::orderBy('display_name')->get()->groupBy(function($item) {
             $parts = explode('-', $item->name, 2);
             return $parts[0];
        });
        
        // Eager load permission yang sudah dimiliki role ini
        $role->load('permissions'); 
        
        // Ambil ID permission yang dimiliki untuk pre-check checkbox
        $rolePermissions = $role->permissions->pluck('id')->toArray(); 

        // Debug: Lihat hasil grouping di log
        // Log::info('Grouped Permissions (Edit): ', $permissions->toArray());
        // Log::info('Role Permissions (Edit): ', $rolePermissions);

        return view('roles.edit', compact('role', 'permissions', 'rolePermissions')); // Kirim rolePermissions ke view
    }

    public function update(Request $request, Role $role)
    {
         // Jangan izinkan mengubah nama 'super-admin'
        if ($role->name === 'super-admin' && $request->input('name') !== 'super-admin') {
             return back()->withInput()->withErrors(['name' => 'Nama role super-admin tidak dapat diubah.']);
        }
        
        $validated = $request->validate([
            // Validasi unik mengabaikan ID role saat ini
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role->id), 'regex:/^[a-z0-9\-]+$/'],
            'display_name' => 'required|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ],[
             'name.regex' => 'Nama Role (Slug) hanya boleh berisi huruf kecil, angka, dan tanda hubung (-).',
        ]);

        $role->update([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'],
        ]);

        // Sync permission, gunakan array kosong jika tidak ada permission yang dipilih
        // Kecuali untuk super-admin, jangan ubah permissionnya
        if ($role->name !== 'super-admin') {
            $role->permissions()->sync($request->input('permissions', [])); 
        }

        return redirect()->route('roles.index')->with('success', 'Data role berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        // Role inti yang tidak boleh dihapus
        $protectedRoles = ['super-admin']; // Tambahkan role lain jika perlu
        if (in_array($role->name, $protectedRoles)) {
            return back()->with('error', 'Role inti (' . $role->display_name . ') tidak dapat dihapus.');
        }

        try {
            // Hapus relasi sebelum menghapus role
            $role->permissions()->detach();
            $role->users()->detach(); // Asumsi ada relasi users() di model Role
            $role->delete();
            return redirect()->route('roles.index')->with('success', 'Role berhasil dihapus.');
        } catch (\Exception $e) {
             Log::error("Gagal menghapus Role ID {$role->id}: " . $e->getMessage());
             return back()->with('error', 'Gagal menghapus role. Pastikan role tidak sedang digunakan.');
        }
    }
}
