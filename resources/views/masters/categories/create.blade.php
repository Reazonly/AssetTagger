@extends('layouts.app')
@section('title', 'Tambah Kategori Baru')
@section('content')
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Tambah Kategori Baru</h1>
    
    <div class="bg-white p-8 rounded-lg shadow-md border max-w-lg">
        <form action="{{ route('master-data.categories.store') }}" method="POST">
            @csrf
            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama Kategori</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700">Kode (Maks. 10 karakter)</label>
                    <input type="text" name="code" id="code" value="{{ old('code') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label for="requires_merk" class="block text-sm font-medium text-gray-700">Membutuhkan Input Merk?</label>
                    <select name="requires_merk" id="requires_merk" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <option value="1" {{ old('requires_merk') == '1' ? 'selected' : '' }}>Ya</option>
                        <option value="0" {{ old('requires_merk') == '0' ? 'selected' : '' }}>Tidak (Menggunakan Tipe)</option>
                    </select>
                </div>
            </div>
            <div class="mt-8 flex gap-4">
                <button type="submit" class="bg-sky-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-sky-700">Simpan</button>
                <a href="{{ route('master-data.categories.index') }}" class="text-gray-600 font-semibold px-4 py-2 rounded-lg hover:bg-gray-200">Batal</a>
            </div>
        </form>
    </div>
@endsection