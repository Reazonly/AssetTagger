@extends('layouts.app')
@section('title', 'Aset')

@section('content')
<div x-data="assetIndex({{ json_encode($assets->items()) }})" class="bg-white rounded-xl shadow-lg p-6 md:p-8">
    
    {{-- Header dan Tombol Aksi --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-gray-200 pb-6 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manajemen Aset</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola semua aset yang terdaftar di sistem.</p>
        </div>
        <div class="flex items-center gap-3 mt-4 md:mt-0">
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="inline-flex items-center gap-2 bg-white text-gray-700 font-semibold px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    Export
                </button>
                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-20" x-cloak>
                    <a href="{{ route('assets.export', request()->query()) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Export Semua (Hasil Filter)</a>
                    <a href="#" @click.prevent="showExportCategoryModal = true; open = false" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Export per Kategori</a>
                </div>
            </div>
            <button @click="showImportModal = true" class="inline-flex items-center gap-2 bg-white text-gray-700 font-semibold px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                Import
            </button>
            <a href="{{ route('assets.create') }}" class="inline-flex items-center gap-2 bg-emerald-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-emerald-700 transition-colors shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                Tambah Aset
            </a>
        </div>
    </div>

{{-- Filter dan Aksi Massal --}}
<div class="grid grid-cols-1 md:grid-cols-2 items-center mb-6 gap-4">

    {{-- GRUP KIRI: PENCARIAN & FILTER --}}
    <div class="flex flex-col md:flex-row gap-4 w-full">
        {{-- Kotak Pencarian --}}
        <form action="{{ route('assets.index') }}" method="GET" class="relative w-full md:w-80">
                @if(request('category_id'))
                    <input type="hidden" name="category_id" value="{{ request('category_id') }}">
                @endif
                <input type="text" 
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari kode, nama, sn..." 
                       class="w-full pl-10 pr-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </form>
        
        {{-- Filter Kategori & Tombol Reset --}}
        <div class="w-full md:w-auto flex items-center gap-2">
             <form action="{{ route('assets.index') }}" method="GET" id="categoryFilterForm">
                <select name="category_id" onchange="this.form.submit()" class="w-full md:w-64 border-2 border-gray-300 rounded-lg shadow-sm py-2 px-3">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </form>
            {{-- Tombol Reset dengan Ikon Baru --}}
            <button @click="resetPage()" class="p-2 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-100" title="Reset Filter & Pilihan">
    <svg xmlns="http://www.w3.org/2000/svg" 
         viewBox="0 0 512 512" 
         fill="currentColor" 
         class="h-5 w-5 text-gray-600">
        <path d="M256 48C141.1 48 48 141.1 48 256s93.1 208 208 208c87.3 0 163.1-52.7 194.5-128h-49.2c-27.8 47.6-79.1 80-145.3 80-88.4 0-160-71.6-160-160s71.6-160 160-160c43.7 0 83.4 17.7 112.1 46.4L304 224h160V64l-55.7 55.7C373.9 86.1 317.6 48 256 48z"/>
    </svg>
</button>
        </div>
    </div>

    {{-- GRUP KANAN: AKSI MASSAL --}}
    <div class="justify-self-end">
        <div class="flex items-center gap-2" x-show="selectedAssets.length > 0" x-cloak>
            <button @click="printSelected" :disabled="selectedAssets.length === 0" class="text-sm bg-gray-200 text-gray-800 font-semibold py-2 px-4 rounded-lg hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap">
                Cetak Terpilih (<span x-text="selectedAssets.length"></span>)
            </button>
            <button @click="exportSelected" :disabled="selectedAssets.length === 0" class="text-sm bg-blue-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap">
                Export Terpilih (<span x-text="selectedAssets.length"></span>)
            </button>
        </div>
    </div>

</div>
    {{-- Tabel Aset --}}
    <div class="overflow-x-auto border border-gray-200 rounded-lg">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b-2 border-black">
                {{-- Header Tabel Baru --}}
                <tr class="divide-x divide-gray-300 text-center">
                    <th scope="col" class="p-4"><input type="checkbox" @click="toggleSelectAll" :checked="areAllVisibleSelected()" class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"></th>
                    <th scope="col" class="px-6 py-3">Kode Aset</th>
                    <th scope="col" class="px-6 py-3">Nama/Tipe Barang</th>
                    <th scope="col" class="px-6 py-3">Kategori</th>
                    <th scope="col" class="px-6 py-3">Sub-Kategori</th>
                    <th scope="col" class="px-6 py-3">Pengguna</th>
                    <th scope="col" class="px-6 py-3">Kondisi</th>
                    @if(auth()->user()->can('edit-asset') || auth()->user()->can('delete-asset'))
                        <th scope="col" class="px-6 py-3">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody class="bg-white">
                {{-- Looping data dengan Alpine.js menggunakan struktur baru --}}
                <template x-for="asset in filteredAssets" :key="asset.id">
                    <tr class="border-b hover:bg-gray-50 divide-x divide-gray-200 text-center">
                        <td class="w-4 p-4"><input type="checkbox" @click="toggleAsset(asset.id)" :checked="isSelected(asset.id)" class="asset-checkbox h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"></td>
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap" x-text="asset.code_asset"></td>
                        <td class="px-6 py-4" x-text="asset.nama_barang"></td>
                        <td class="px-6 py-4" x-text="asset.category ? asset.category.name : 'N/A'"></td>
                        <td class="px-6 py-4" x-text="asset.sub_category ? asset.sub_category.name : '-'"></td>
                        <td class="px-6 py-4" x-text="asset.asset_user ? asset.asset_user.nama : 'N/A'"></td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                  :class="{
                                      'bg-green-100 text-green-800': asset.kondisi === 'Baik',
                                      'bg-red-100 text-red-800': asset.kondisi === 'Rusak',
                                      'bg-yellow-100 text-yellow-800': asset.kondisi === 'Perbaikan'
                                  }"
                                  x-text="asset.kondisi">
                            </span>
                        </td>
                        @if(auth()->user()->can('edit-asset') || auth()->user()->can('delete-asset'))
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            @can('view-asset')
                                <a :href="`/assets/${asset.id}`" class="font-medium text-emerald-600 hover:text-emerald-800">Lihat</a>
                            @endcan
                            @can('edit-asset')
                                <a :href="`/assets/${asset.id}/edit`" class="font-medium text-blue-600 hover:text-blue-800 ml-4">Edit</a>
                            @endcan
                            @can('delete-asset')
                                <form :action="`/assets/${asset.id}`" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aset ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-medium text-red-600 hover:text-red-800 ml-4">Hapus</button>
                                </form>
                            @endcan
                        </td>
                        @endif
                    </tr>
                </template>
                
                {{-- Tampilan saat data tidak ditemukan --}}
                <template x-if="filteredAssets.length === 0">
                    <tr>
                        <td colspan="{{ auth()->user()->can('edit-asset') || auth()->user()->can('delete-asset') ? '8' : '7' }}" class="text-center py-10 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Aset tidak ditemukan</h3>
                            <p class="mt-1 text-sm text-gray-500">Coba ubah filter atau kata kunci pencarian Anda.</p>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    @if ($assets->hasPages())
    <div class="mt-6">
        {{ $assets->appends(request()->query())->links() }}
    </div>
    @endif
    
   {{-- Modal Import --}}
<div x-show="showImportModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div @click.away="showImportModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Import Data Aset</h3>
        <form action="{{ route('assets.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="text-sm text-gray-600 mb-4 space-y-2">
            </div>
            <p class="text-sm text-gray-600 mb-4">
                        Unggah file Excel. Pastikan kolom header sesuai template export berikut : <strong>Nama Barang, Kategori, Sub Kategori, Perusahaan Pemilik, Merk/Tipe, Serial Number, Pengguna Aset, Jabatan Pengguna, Departemen Pengguna, Perusahaan Pengguna, Kondisi, Lokasi, Jumlah, Satuan, Tanggal Pembelian, Bulan Pembelian, Tahun Pembelian, Harga Total(Rp), Nomor PO, Nomor BAST(WAJIB), Kode Aktiva, Sumber Dana, Item Termasuk, Peruntukan, Keterangan, Deskripsi.
                    </strong><div class="text-sm text-gray-600 mb-4"><strong> diluar dari template export akan masuk ke dalam detail spesifikasi.</strong></div>
                    </p>
            <input type="file" name="file" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100"/>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="showImportModal = false" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-md hover:bg-emerald-700">Import</button>
            </div>
        </form>
    </div>
</div>

<div x-show="showExportCategoryModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div @click.away="showExportCategoryModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Export Berdasarkan Kategori</h3>
            <form action="{{ route('assets.export') }}" method="GET">
                <select name="category_id_export" class="w-full border-gray-300 rounded-lg shadow-sm py-2 px-3">
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="showExportCategoryModal = false" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-md hover:bg-blue-700">Export</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    console.log('Aset yang diterima dari server:', @json($assets->items()));
    function assetIndex(initialAssets) {
        return {
            showImportModal: false,
            showExportCategoryModal: false,
            allAssets: initialAssets,
            filteredAssets: [],
            selectedAssets: [],
            searchQuery: '',
            
            init() {
                this.selectedAssets = JSON.parse(localStorage.getItem('selectedAssets')) || [];
                this.filteredAssets = this.allAssets;
                this.$watch('searchQuery', () => this.filterAssets());
            },

            saveSelected() {
                localStorage.setItem('selectedAssets', JSON.stringify(this.selectedAssets));
            },
            
            filterAssets() {
                if (!this.searchQuery.trim()) {
                    this.filteredAssets = this.allAssets;
                    return;
                }
                const keywords = this.searchQuery.toLowerCase().split(' ').filter(k => k);
                this.filteredAssets = this.allAssets.filter(asset => {
                    const searchableContent = [
                        asset.code_asset,
                        asset.nama_barang,
                        asset.serial_number,
                        asset.category ? asset.category.name : '',
                        asset.sub_category ? asset.sub_category.name : '',
                        asset.asset_user ? asset.asset_user.nama : ''
                    ].join(' ').toLowerCase();
                    return keywords.every(keyword => searchableContent.includes(keyword));
                });
            },

            // --- FUNGSI BARU UNTUK TOMBOL RESET ---
            resetPage() {
                // 1. Hapus semua pilihan
                this.selectedAssets = [];
                this.saveSelected();
                // 2. Kembali ke halaman utama (menghapus filter kategori)
                window.location.href = '{{ route('assets.index') }}';
            },

            toggleAsset(assetId) {
                const index = this.selectedAssets.indexOf(assetId);
                if (index === -1) {
                    this.selectedAssets.push(assetId);
                } else {
                    this.selectedAssets.splice(index, 1);
                }
                this.saveSelected();
            },

            toggleSelectAll(event) {
                if (event.target.checked) {
                    this.filteredAssets.forEach(asset => {
                        if (!this.isSelected(asset.id)) {
                            this.selectedAssets.push(asset.id);
                        }
                    });
                } else {
                    const idsToDeselect = this.filteredAssets.map(asset => asset.id);
                    this.selectedAssets = this.selectedAssets.filter(id => !idsToDeselect.includes(id));
                }
                this.saveSelected();
            },

            areAllVisibleSelected() {
                if (this.filteredAssets.length === 0) return false;
                const visibleIds = this.filteredAssets.map(asset => asset.id);
                return visibleIds.every(id => this.isSelected(id));
            },

            isSelected(assetId) {
                return this.selectedAssets.includes(assetId);
            },

            printSelected() {
                const query = this.selectedAssets.map(id => `ids[]=${id}`).join('&');
                window.open(`{{ route('assets.print') }}?${query}`, '_blank');
            },

            exportSelected() {
                const query = this.selectedAssets.map(id => `ids[]=${id}`).join('&');
                window.location.href = `{{ route('assets.export') }}?${query}`;
            }
        }
    }
</script>
@endpush
@endsection