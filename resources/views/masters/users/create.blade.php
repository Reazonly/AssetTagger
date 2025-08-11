@extends('layouts.app')

@section('title', 'Tambah Pengguna Aset Baru')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Tambah Pengguna Aset Baru</h1>
        <a href="{{ route('master-data.users.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900">
            &larr; Kembali ke Daftar
        </a>
    </div>
    
    <div class="bg-white p-8 rounded-lg shadow-md border">
        <form action="{{ route('master-data.users.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label for="nama_pengguna" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                    <input type="text" name="nama_pengguna" id="nama_pengguna" value="{{ old('nama_pengguna') }}" required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('nama_pengguna') border-red-500 @enderror">
                    @error('nama_pengguna') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                     <div class="md:col-span-2">
            <label for="email" class="block text-sm font-medium text-gray-700">Alamat Email (Opsional)</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}"
           class="mt-1 block w-full ...">
             <p class="text-xs text-gray-500 mt-1">Isi hanya jika pengguna ini butuh akses login ke sistem.</p>
                 @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

                <div>
                    <label for="jabatan" class="block text-sm font-medium text-gray-700">Jabatan (Opsional)</label>
                    <input type="text" name="jabatan" id="jabatan" value="{{ old('jabatan') }}"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('jabatan') border-red-500 @enderror">
                    @error('jabatan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="departemen" class="block text-sm font-medium text-gray-700">Departemen (Opsional)</label>
                    <input type="text" name="departemen" id="departemen" value="{{ old('departemen') }}"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('departemen') border-red-500 @enderror">
                    @error('departemen') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
    <label for="password" class="block text-sm font-medium text-gray-700">Password (Opsional)</label>
    <input type="password" name="password" id="password"
           class="mt-1 block w-full ...">
    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
</div>

                <div>
    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
    <input type="password" name="password_confirmation" id="password_confirmation"
           class="mt-1 block w-full ...">
</div>
            </div>

            <div class="mt-8 flex gap-4">
                <button type="submit" class="bg-sky-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-sky-700">
                    Simpan Pengguna
                </button>
                <a href="{{ route('master-data.users.index') }}" class="text-gray-600 font-semibold px-4 py-2 rounded-lg hover:bg-gray-200">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection