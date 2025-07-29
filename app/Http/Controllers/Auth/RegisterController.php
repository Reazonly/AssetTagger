<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    // Menampilkan halaman form registrasi
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    // Memproses data dari form registrasi
    public function register(Request $request)
    {
        // 1. Validasi data input
        $validator = Validator::make($request->all(), [
            'nama_pengguna' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return redirect('register')
                        ->withErrors($validator)
                        ->withInput();
        }

        // 2. Buat pengguna baru
        $user = User::create([
            'nama_pengguna' => $request->nama_pengguna,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Enkripsi password
        ]);

        // 3. Login-kan pengguna yang baru dibuat
        Auth::login($user);

        // 4. Arahkan ke halaman utama
        return redirect()->route('assets.index');
    }
}