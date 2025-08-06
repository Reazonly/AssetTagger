@extends('layouts.app')

@section('title', 'Manajemen Aset')

@section('content')
{{-- 
  Alpine.js digunakan di sini untuk mengelola state:
  - selectedItems: Menyimpan ID dari aset yang dipilih.
  - isImportModalOpen: Mengontrol modal/popup untuk impor data.
  - buildWithIds(url): Fungsi bantuan untuk membuat URL dengan ID yang dipilih.
--}}
<div x-data="{ 
        selectedItems: [],
        isImportModalOpen: false,
        buildWithIds(url) {
            if (this.selectedItems.length === 0) return '#';
            const queryParams = this.selectedItems.map(id => `ids[]=${id}`).join('&');
            return `${url}?${queryParams}`;
        }
     }" 
     class="bg-white rounded-xl shadow-lg p-6 md:p-8">
    
    {{-- Header Halaman --}}
    <div class="flex flex-col sm:flex-row justify-between items-start mb-6 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Manajemen Aset</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola, cari, dan filter semua data aset.</p>
        </div>
        <div class="flex items-center space-x-2">
            {{-- Tombol Impor dengan Ikon --}}
            <button @click="isImportModalOpen = true" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                Impor
            </button>
            
            {{-- Tombol Tambah Aset dengan Ikon --}}
            <a href="{{ route('assets.create') }}" class="inline-flex items-center px-4 py-2 bg-sky-600 text-white font-semibold rounded-lg hover:bg-sky-700 shadow-md transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Tambah Aset
            </a>
        </div>
    </div>

    {{-- Filter, Pencarian, dan Aksi Massal --}}
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <form action="{{ route('assets.index') }}" method="GET" class="w-full max-w-sm">
            <div class="relative">
                <input type="text" name="search" placeholder="Cari kode, nama, pengguna..." value="{{ request('search') }}" class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500">
                <svg class="h-5 w-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
        </form>

        <div class="flex items-center space-x-2">
            {{-- Tombol Cetak dengan Ikon (dinamis) --}}
            <a :href="buildWithIds('{{ route('assets.print') }}')"
               @click="if (selectedItems.length === 0) $event.preventDefault()"
               :class="{ 'opacity-50 cursor-not-allowed': selectedItems.length === 0 }"
               target="_blank"
               class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300 shadow-sm transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v3a2 2 0 002 2h6a2 2 0 002-2v-3h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v3h6v-3z" clip-rule="evenodd" />
                </svg>
                Cetak Terpilih
            </a>
            
            {{-- Tombol Ekspor dengan Ikon (dinamis) --}}
            <a :href="buildWithIds('{{ route('assets.export') }}')"
               @click="if (selectedItems.length === 0) $event.preventDefault()"
               :class="{ 'opacity-50 cursor-not-allowed': selectedItems.length === 0 }"
               class="inline-flex items-center px-4 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 shadow-sm transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 00-2.25 2.25v9a2.25 2.25 0 002.25 2.25h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25H15M9 12l3 3m0 0l3-3m-3 3V2.25" />
                </svg>
                Ekspor Terpilih
            </a>
        </div>
    </div>
    
    {{-- Tabel Aset --}}
    <div class="overflow-x-auto">
        <table class="w-full min-w-full border-collapse">
            {{-- PERUBAHAN: Garis bawah header lebih tebal dan hitam --}}
            <thead class="bg-gray-100 border-b-2 border-black">
                <tr>
                    {{-- PERUBAHAN: Menambahkan class border pada setiap header --}}
                    <th class="px-6 py-3 w-4 border border-gray-300">
                        <input type="checkbox"
                               @click="selectedItems = $event.target.checked ? {{ $assets->pluck('id')->map(fn($id) => (string)$id) }} : []"
                               class="rounded">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border border-gray-300">Kode Aset</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border border-gray-300">Nama Barang</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border border-gray-300">Kategori</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border border-gray-300">Pengguna</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider border border-gray-300">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($assets as $asset)
                    <tr class="hover:bg-gray-50" :class="{'bg-sky-50': selectedItems.includes('{{ (string)$asset->id }}')}">
                        {{-- PERUBAHAN: Menambahkan class border pada setiap sel data --}}
                        <td class="px-6 py-4 border border-gray-300">
                            <input type="checkbox" x-model="selectedItems" value="{{ (string)$asset->id }}" class="rounded">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600 border border-gray-300">{{ $asset->code_asset }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border border-gray-300">{{ $asset->nama_barang }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border border-gray-300">{{ optional($asset->category)->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border border-gray-300">{{ optional($asset->user)->nama_pengguna ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium border border-gray-300">
                            <div class="flex gap-4 justify-end">
                                <a href="{{ route('assets.show', $asset) }}" class="text-green-600 hover:text-green-900 font-semibold">Lihat</a>
                                <a href="{{ route('assets.edit', $asset) }}" class="text-blue-600 hover:text-blue-900 font-semibold">Edit</a>
                                <form action="{{ route('assets.destroy', $asset) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aset ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-semibold">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 border border-gray-300">Tidak ada data aset ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($assets->hasPages())
        <div class="mt-6">{{ $assets->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection