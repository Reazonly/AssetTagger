@extends('layouts.app')
@section('title', 'Daftar Aset')
@section('content')
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        {{-- Judul Halaman --}}
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Daftar Aset</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola dan cari semua aset perusahaan hideung</p>
        </div>
        
                {{-- tes --}}

        {{-- Grup Tombol & Pencarian --}}
        <div class="flex items-center gap-2 w-full sm:w-auto">
             {{-- Form Pencarian --}}
            <form action="{{ route('assets.index') }}" method="GET" class="w-full sm:w-auto flex-grow">
                <div class="relative">
                    <input type="text" name="search" placeholder="Cari aset..." value="{{ $search ?? '' }}" class="w-full sm:w-64 pl-4 pr-10 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <button type="submit" class="absolute right-0 top-0 mt-2 mr-3 text-gray-500 hover:text-emerald-600">
                        <svg class="h-5 w-5" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </div>
            </form>
            {{-- Tombol Import --}}
            <button onclick="document.getElementById('importModal').classList.remove('hidden')" class="bg-gray-700 text-white font-semibold px-4 py-2 rounded-lg hover:bg-gray-800 transition-colors flex-shrink-0">
                Import
            </button>
            {{-- Tombol Tambah Aset --}}
            <a href="{{ route('assets.create') }}" class="bg-emerald-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-emerald-700 transition-colors flex-shrink-0">
                + Tambah
            </a>
        </div>
    </div>

    <div class="overflow-hidden border border-gray-200 rounded-lg shadow">
        <table class="w-full min-w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase border-b border-r border-gray-200">Kode Aset</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase border-b border-r border-gray-200">Nama Barang</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase border-b border-r border-gray-200">Pengguna</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase border-b border-r border-gray-200">Kondisi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase border-b border-gray-200">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white">
                @forelse ($assets as $asset)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-800 border-r border-gray-200">{{ $asset->code_asset }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600 border-r border-gray-200">{{ $asset->nama_barang }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600 border-r border-gray-200">{{ $asset->user->nama_pengguna ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600 border-r border-gray-200">{{ $asset->kondisi }}</td>
                        <td class="px-6 py-4 text-left text-sm whitespace-nowrap">
                            <a href="{{ route('assets.show', $asset->id) }}" class="font-semibold text-emerald-600 hover:text-emerald-800">Lihat</a>
                            <a href="{{ route('assets.edit', $asset->id) }}" class="font-semibold text-emerald-600 hover:text-emerald-800 ml-4">Edit</a>
                            {{-- Tombol Hapus --}}
                            <form action="{{ route('assets.destroy', $asset->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aset ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-semibold text-red-600 hover:text-red-800 ml-4">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-10 text-gray-500">Aset tidak ditemukan. Coba kata kunci lain.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Link Paginasi --}}
    <div class="mt-6">
        {{ $assets->appends(['search' => $search])->links() }}
    </div>

    {{-- Modal untuk Import Excel --}}
    <div id="importModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-20">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-medium text-gray-900">Import Data dari Excel</h3>
            <form action="{{ route('assets.import') }}" method="POST" enctype="multipart/form-data" class="mt-4">
                @csrf
                <input type="file" name="file" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100" required>
                <div class="mt-6 flex justify-end space-x-2">
                    <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700">Upload & Import</button>
                </div>
            </form>
        </div>
    </div>
@endsection
