@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="bg-white rounded-xl shadow-lg p-6 md:p-8 max-w-2xl mx-auto">

    <div class="border-b border-gray-200 pb-6 mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Profil Saya</h1>
        <p class="text-sm text-gray-500 mt-1">Perbarui informasi dan password akun Anda.</p>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md" role="alert">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('profile.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <div>
                <label for="nama_pengguna" class="block text-sm font-medium text-gray-700">Nama Pengguna</label>
                <input type="text" id="nama_pengguna" value="{{ auth()->user()->nama_pengguna }}" class="mt-1 block w-full bg-gray-100 border-gray-300 rounded-md shadow-sm cursor-not-allowed" readonly>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" value="{{ auth()->user()->email }}" class="mt-1 block w-full bg-gray-100 border-gray-300 rounded-md shadow-sm cursor-not-allowed" readonly>
            </div>

            <div class="border-t pt-6">
                <h2 class="text-xl font-semibold text-gray-800">Ubah Password</h2>
                <div class="mt-4 space-y-4">
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700">Password Saat Ini</label>
                        <input type="password" name="current_password" id="current_password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password Baru</label>
                        <input type="password" name="password" id="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end">
            <button type="submit" class="bg-sky-600 text-white font-semibold px-5 py-2 rounded-lg hover:bg-sky-700 shadow-sm">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection