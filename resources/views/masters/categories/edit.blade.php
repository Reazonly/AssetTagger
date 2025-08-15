@extends('layouts.app')
@section('title', 'Edit Kategori')
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Edit Kategori: {{ $category->name }}</h1>
         <a href="{{ route('master-data.categories.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900">
            &larr; Kembali
        </a>
    </div>
    <div class="bg-white p-8 rounded-lg shadow-md border">
        <form action="{{ route('master-data.categories.update', $category->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama Kategori</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" required class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3 @error('name') border-red-500 @enderror">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700">Kode (Maks. 10 karakter)</label>
                    <input type="text" name="code" id="code" value="{{ old('code', $category->code) }}" required maxlength="10" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3 @error('code') border-red-500 @enderror">
                    @error('code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="mt-8 flex gap-4">
                <button type="submit" class="bg-sky-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-sky-700">Simpan Perubahan</button>
                <a href="{{ route('master-data.categories.index') }}" class="text-gray-600 font-semibold px-4 py-2 rounded-lg hover:bg-gray-200">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
