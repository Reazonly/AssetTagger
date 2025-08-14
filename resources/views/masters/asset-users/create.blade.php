@extends('layouts.app')
@section('title', 'Tambah Pengguna Aset Baru')
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Tambah Pengguna Aset Baru</h1>
        <a href="{{ route('master-data.asset-users.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900">
            &larr; Kembali ke Daftar
        </a>
    </div>
    
    <div class="bg-white p-8 rounded-lg shadow-md border">
        <form action="{{ route('master-data.asset-users.store') }}" method="POST">
            @csrf
            <div class="space-y-6">
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                    <input type="text" name="nama" id="nama" value="{{ old('nama') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 @error('nama') border-red-500 @enderror">
                    @error('nama') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="jabatan" class="block text-sm font-medium text-gray-700">Jabatan (Opsional)</label>
                    <input type="text" name="jabatan" id="jabatan" value="{{ old('jabatan') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 @error('jabatan') border-red-500 @enderror">
                    @error('jabatan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="departemen" class="block text-sm font-medium text-gray-700">Departemen (Opsional)</label>
                    <input type="text" name="departemen" id="departemen" value="{{ old('departemen') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 @error('departemen') border-red-500 @enderror">
                    @error('departemen') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="company_id" class="block text-sm font-medium text-gray-700">Perusahaan (Opsional)</label>
                    <select name="company_id" id="company_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 @error('company_id') border-red-500 @enderror">
                        <option value="">-- Pilih Perusahaan --</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                        @endforeach
                    </select>
                    @error('company_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="mt-8 flex gap-4">
                <button type="submit" class="bg-sky-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-sky-700">
                    Simpan Pengguna
                </button>
                <a href="{{ route('master-data.asset-users.index') }}" class="text-gray-600 font-semibold px-4 py-2 rounded-lg hover:bg-gray-200">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
