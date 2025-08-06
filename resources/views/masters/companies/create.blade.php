@extends('layouts.app')
@section('title', 'Tambah Perusahaan Baru')
@section('content')
    <form action="{{ route('master-data.companies.store') }}" method="POST">
        @csrf
        
        {{-- Header Halaman --}}
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Tambah Perusahaan Baru</h1>
            <p class="text-sm text-gray-500 mt-1">Isi detail perusahaan yang akan ditambahkan.</p>
        </div>

        {{-- Konten Form --}}
        <div class="bg-white p-8 rounded-lg shadow-md border">
            <div class="space-y-6 max-w-lg">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama Perusahaan <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-sky-500 focus:border-sky-500">
                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700">Kode <span class="text-red-500">*</span></label>
                    <input type="text" name="code" id="code" value="{{ old('code') }}" required maxlength="10"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-sky-500 focus:border-sky-500">
                    @error('code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- Tombol Aksi di Bawah --}}
        <div class="mt-8 pt-6 border-t flex justify-end items-center gap-3">
            <a href="{{ route('master-data.companies.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 py-2 px-4 rounded-lg bg-gray-200 hover:bg-gray-300">Batal</a>
            <button type="submit" class="bg-sky-600 text-white font-semibold px-5 py-2 rounded-lg hover:bg-sky-700 shadow-md">Simpan</button>
        </div>
    </form>
@endsection