@extends('layouts.app')
@section('title', 'Edit Sub-Kategori')
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('master-data.sub-categories.show', $subCategory->category_id) }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900">&larr; Kembali</a>
        <h1 class="text-3xl font-bold text-gray-800 mt-2">Edit Sub-Kategori: <span class="text-sky-600">{{ $subCategory->name }}</span></h1>
    </div>
    <div class="bg-white p-8 rounded-lg shadow-md border" x-data="{ specFields: {{ json_encode(old('spec_fields', $subCategory->spec_fields ?? [''])) }} }">
        <form action="{{ route('master-data.sub-categories.update', $subCategory->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama Sub-Kategori</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $subCategory->name) }}" required class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3">
                </div>

                <div class="pt-4 border-t">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Input Wajib di Form Aset</label>
                    <div class="space-y-2">
                        <label class="flex items-center"><input type="radio" name="input_type" value="none" class="mr-2" {{ old('input_type', $subCategory->input_type) == 'none' ? 'checked' : '' }}> Tidak ada</label>
                        <label class="flex items-center"><input type="radio" name="input_type" value="merk" class="mr-2" {{ old('input_type', $subCategory->input_type) == 'merk' ? 'checked' : '' }}> Perlu Merk</label>
                        <label class="flex items-center"><input type="radio" name="input_type" value="tipe" class="mr-2" {{ old('input_type', $subCategory->input_type) == 'tipe' ? 'checked' : '' }}> Hanya Tipe</label>
                        <label class="flex items-center"><input type="radio" name="input_type" value="merk_dan_tipe" class="mr-2" {{ old('input_type', $subCategory->input_type) == 'merk_dan_tipe' ? 'checked' : '' }}> Perlu Merk & Tipe (2 Opsi)</label>
                    </div>
                </div>

                <div class="pt-4 border-t">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kolom Spesifikasi Detail (Opsional)</label>
                    <p class="text-xs text-gray-500 mb-3">Tambahkan field input yang akan muncul di form aset jika sub-kategori ini dipilih.</p>
                    <template x-for="(field, index) in specFields" :key="index">
                        <div class="flex items-center gap-2 mb-2">
                            <input type="text" name="spec_fields[]" x-model="specFields[index]" class="block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3" placeholder="Nama Field">
                            <button type="button" @click="specFields.splice(index, 1)" class="text-red-500 hover:text-red-700">&times;</button>
                        </div>
                    </template>
                    <button type="button" @click="specFields.push('')" class="text-sm text-emerald-600 font-semibold mt-2">+ Tambah Field</button>
                </div>
            </div>
            <div class="mt-8 flex gap-4">
                <button type="submit" class="bg-sky-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-sky-700">Simpan Perubahan</button>
                <a href="{{ route('master-data.sub-categories.show', $subCategory->category_id) }}" class="text-gray-600 font-semibold px-4 py-2 rounded-lg hover:bg-gray-200">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
