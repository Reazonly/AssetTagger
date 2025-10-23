@extends('layouts.app')

@section('title', 'Tambah Pengguna Login Baru')

@section('content')
<div class="max-w-2xl mx-auto">
  
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Tambah Pengguna Login</h1>
        <a href="{{ route('users.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 transition-colors py-2 px-4 rounded-lg bg-gray-200 hover:bg-gray-300 border border-black inline-flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
            Kembali
        </a>
    </div>
    
    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md shadow" role="alert">
            <p class="font-bold">Terjadi Kesalahan</p>
            <ul class="list-disc list-inside mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <div class="bg-white p-8 rounded-lg shadow-md border">
        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label for="nama_pengguna" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                    <input type="text" name="nama_pengguna" id="nama_pengguna" value="{{ old('nama_pengguna') }}" required class="block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3 @error('nama_pengguna') border-red-500 @enderror">
                    @error('nama_pengguna') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="email" class="block text-sm font-medium text-gray-700">Alamat Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required class="block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3 @error('email') border-red-500 @enderror">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="roles" class="block text-sm font-medium text-gray-700">Role</label>
                    <select name="roles[]" id="roles" class="block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3 @error('roles') border-red-500 @enderror">
                        <option value="">-- Pilih Satu Role --</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" {{ old('roles.0') == $role->id ? 'selected' : '' }}>
                                {{ $role->display_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('roles') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Hak Akses Perusahaan</label>
                    <p class="text-xs text-gray-500 mb-2">Pilih perusahaan mana saja yang datanya bisa dilihat oleh pengguna ini.</p>
                    <div class="mt-2 p-4 border-2 border-gray-400 rounded-md max-h-48 overflow-y-auto">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2">
                            @foreach ($companies as $company)
                                <label class="flex items-center">
                                    <input type="checkbox" name="companies[]" value="{{ $company->id }}"
                                           class="h-4 w-4 text-sky-600 border-gray-300 rounded focus:ring-sky-500"
                                           {{ (is_array(old('companies')) && in_array($company->id, old('companies'))) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-700">{{ $company->name }} ({{ $company->code }})</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    @error('companies') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    @error('companies.*') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" id="password" required class="block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3 @error('password') border-red-500 @enderror">
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required class="block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3">
                </div>
            </div>

            <div class="mt-8 flex gap-4">
                <button type="submit" class="bg-sky-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-sky-700">
                    Simpan Pengguna
                </button>
                <a href="{{ route('users.index') }}" class="text-gray-600 font-semibold px-4 py-2 rounded-lg hover:bg-gray-200">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection