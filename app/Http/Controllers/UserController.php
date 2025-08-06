<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('id', '!=', auth()->id())->latest()->paginate(15);
        return view('users.index', compact('users'));
    }

    public function updateRole(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat mengubah role diri sendiri.');
        }

        $validated = $request->validate([
            'role' => ['required', Rule::in(['admin', 'viewer', 'user'])],
        ]);

        $user->role = $validated['role'];
        $user->save();

        return redirect()->route('users.index')->with('success', 'Role untuk pengguna ' . $user->nama_pengguna . ' berhasil diperbarui.');
    }
}