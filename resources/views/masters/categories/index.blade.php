@extends('layouts.app')

@section('title', 'Master Data Kategori')

@section('content')
<div x-data="{ showImportModal: false }" class="bg-white rounded-xl shadow-lg p-6 md:p-8">

    {{-- Header Halaman --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-gray-200 pb-6 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manajemen Kategori</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola semua kategori aset di perusahaan.</p>
        </div>
        {{-- Tombol Aksi --}}
        <div class="flex items-center gap-3 mt-4 md:mt-0">
            <button @click="showImportModal = true" class="inline-flex items-center gap-2 bg-white text-gray-700 font-semibold px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                Import
            </button>
            <a href="{{ route('master-data.categories.create') }}" class="inline-flex items-center gap-2 bg-emerald-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-emerald-700 transition-colors shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                Tambah Kategori
            </a>
        </div>
    </div>

<div class="mb-6">
    <form action="{{ route('master-data.categories.index') }}" method="GET">
        <div class="relative w-full md:w-1/3">
            <input 
                type="text" 
                name="search" 
                placeholder="Cari Kategori atau Kode" 
                value="{{ request('search') }}" 
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                onchange="this.form.submit()">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>
    </form>
</div>

   
    <div class="overflow-x-auto border border-gray-200 rounded-lg">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b-2 border-black">
                <tr  class="text-center divide-x divide-gray-300">
                    <th scope="col" class="px-6 py-3">Nama Kategori</th>
                    <th scope="col" class="px-6 py-3">Kode</th>
                    <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y">
                @forelse ($categories as $category)
                    <tr class="border-b hover:bg-gray-50 divide-x divide-gray-200 text-center">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $category->name }}</td>
                        <td class="px-6 py-4">{{ $category->code }}</td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-4">
                                <a href="{{ route('master-data.categories.edit', $category->id) }}" class="font-medium text-blue-600 hover:text-blue-800">Edit</a>
                                <form action="{{ route('master-data.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus kategori ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-medium text-red-600 hover:text-red-800">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-10 text-gray-500">Tidak ada data kategori yang cocok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div x-show="showImportModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div @click.away="showImportModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Import Data Kategori</h3>
            <form action="{{ route('master-data.categories.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <p class="text-sm text-gray-600 mb-4">
                    Unggah file Excel dengan kolom: <strong>nama kategori dan kode kategori</strong>.
                </p>
                <input type="file" name="file" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100"/>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="showImportModal = false" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-md hover:bg-emerald-700">Import</button>
                </div>
            </form>
        </div>
    </div>

    
    @if ($categories->hasPages())
        <div class="mt-6">{{ $categories->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
