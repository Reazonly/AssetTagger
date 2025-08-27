@extends('layouts.app')
@section('title', 'Tambah Sub-Kategori')
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mt-2">Tambah Sub-Kategori untuk <span class="text-sky-600">{{ $category->name }}</span></h1>
    </div>
    <div class="bg-white p-8 rounded-lg shadow-md border" x-data="{ specFields: [''] }">
        <form action="{{ route('master-data.sub-categories.store', $category->id) }}" method="POST">
            @csrf
            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama Sub-Kategori</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3">
                </div>

                <div class="pt-4 border-t">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Input Wajib di Form Aset</label>
                    <div class="space-y-2">
                        <label class="flex items-center"><input type="radio" name="input_type" value="none" class="mr-2" checked> Tidak ada</label>
                        <label class="flex items-center"><input type="radio" name="input_type" value="merk" class="mr-2"> Perlu Merk</label>
                        <label class="flex items-center"><input type="radio" name="input_type" value="tipe" class="mr-2"> Hanya Tipe</label>
                        <label class="flex items-center"><input type="radio" name="input_type" value="merk_dan_tipe" class="mr-2"> Perlu Merk & Tipe (2 Opsi)</label>
                    </div>
                </div>

               {{-- Field Spesifikasi Kustom --}}
<div class="mt-6" x-data="{ specFields: [{ name: '', type: 'text' }] }">
    <label class="block text-sm font-medium text-gray-700 mb-2">Field Spesifikasi Kustom (Opsional)</label>
    <template x-for="(field, index) in specFields" :key="index">
        <div class="flex items-center gap-2 mb-2">
            
            {{-- PERBAIKAN: Atribut 'name' dan 'x-model' diubah --}}
            <input type="text" :name="`spec_fields[${index}][name]`" x-model="field.name" placeholder="Nama Field (Contoh: Ukuran Layar)" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3">
            
            <select :name="`spec_fields[${index}][type]`" x-model="field.type" class="border-2 border-gray-400 rounded-md shadow-sm py-2 px-3">
                <option value="text">Teks</option>
                <option value="number">Angka</option>
                <option value="textarea">Area Teks</option>
            </select>

            <button type="button" @click="specFields.splice(index, 1)" x-show="index > 0" class="p-2 text-red-500 hover:bg-red-100 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
            </button>
        </div>
    </template>
    
    <button type="button" @click="specFields.push({ name: '', type: 'text' })" class="text-sm text-emerald-600 font-semibold hover:text-emerald-800">+ Tambah Field</button>
</div>
            </div>
            <div class="mt-8 flex gap-4">
                <button type="submit" class="bg-sky-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-sky-700">Simpan</button>
                <a href="{{ route('master-data.sub-categories.show', $category->id) }}" class="text-gray-600 font-semibold px-4 py-2 rounded-lg hover:bg-gray-200">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
