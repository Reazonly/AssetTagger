@extends('layouts.app')
@section('title', 'Aset')

@section('content')

{{-- Menampilkan error --}}
@if ($errors->any())
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md" role="alert">
        <p class="font-bold">Terjadi Kesalahan</p>
        <ul class="mt-2 list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Wrapper utama dengan Alpine data --}}
<div x-data="assetIndex({{ json_encode($assets->items()) }})" class="bg-white rounded-xl shadow-lg p-6 md:p-8">
    
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-gray-200 pb-6 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manajemen Aset</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola semua aset yang terdaftar di sistem.</p>
        </div>
        <div class="flex items-center gap-3 mt-4 md:mt-0">
            {{-- Tombol Import (TETAP) --}}
            <button @click="showImportModal = true" class="inline-flex items-center gap-2 bg-white text-gray-700 font-semibold px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors h-10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                Import
            </button>
            {{-- Tombol Tambah Aset (TETAP) --}}
            <a href="{{ route('assets.create') }}" class="inline-flex items-center gap-2 bg-emerald-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-emerald-700 transition-colors shadow-sm h-10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                Tambah Aset
            </a>
        </div>
    </div>

    {{-- BLOK FILTER (TETAP) --}}
    <form method="GET" action="{{ route('assets.index') }}" class="mb-6"
        x-data="{ 
            selectedCategory: '{{ request('category_id') ?? '' }}',
            selectedSubCategory: '{{ request('sub_category_id') ?? '' }}',
            allSubCategories: {{ $subCategories->mapWithKeys(fn($sc) => [$sc->id => ['name' => $sc->name, 'category_id' => $sc->category_id]])->toJson() }},
            filteredSubCategories: {},
            filterSubCategories() {
                this.filteredSubCategories = Object.fromEntries(
                    Object.entries(this.allSubCategories).filter(([id, sub]) => !this.selectedCategory || sub.category_id == this.selectedCategory)
                );
                if (this.selectedSubCategory && !this.filteredSubCategories[this.selectedSubCategory]) {
                    this.selectedSubCategory = '';
                }
            }
        }"
        x-init="filterSubCategories()">
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4 p-4 border border-gray-200 rounded-lg bg-gray-50">
            {{-- Filter Kategori --}}
            <select name="category_id" x-model="selectedCategory" @change="filterSubCategories" class="w-full border-2 border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:border-sky-500 focus:ring-sky-500 text-sm h-10">
                <option value="">Filter Kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" x-bind:selected="selectedCategory == {{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            {{-- Filter Sub-Kategori --}}
            <select name="sub_category_id" x-model="selectedSubCategory" class="w-full border-2 border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:border-sky-500 focus:ring-sky-500 text-sm h-10">
                <option value="">Filter Sub-Kategori</option>
                <template x-for="(sub, id) in filteredSubCategories" :key="id">
                    <option :value="id" :selected="id == selectedSubCategory" x-text="sub.name"></option> 
                </template>
            </select>
            {{-- Filter Perusahaan --}}
            <select name="company_id" class="w-full border-2 border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:border-sky-500 focus:ring-sky-500 text-sm h-10">
                <option value="">Filter Perusahaan</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}" {{ (request('company_id') == $company->id) ? 'selected' : '' }}>{{ $company->name }}</option>
                @endforeach
            </select>
            {{-- Filter Lokasi --}}
            <select name="location" class="w-full border-2 border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:border-sky-500 focus:ring-sky-500 text-sm h-10">
                <option value="">Filter Lokasi</option>
                @isset($locations)
                    @foreach($locations as $location)
                        <option value="{{ $location }}" {{ (request('location') == $location) ? 'selected' : '' }}>{{ $location }}</option>
                    @endforeach
                @endisset
            </select>
            {{-- Filter Pengguna --}}
            <select name="asset_user_id" class="w-full border-2 border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:border-sky-500 focus:ring-sky-500 text-sm h-10">
                <option value="">Filter Pengguna Saat Ini</option>
                @isset($assetUsers) 
                    @foreach($assetUsers as $user)
                        <option value="{{ $user->id }}" {{ (request('asset_user_id') == $user->id) ? 'selected' : '' }}>{{ $user->nama }}</option>
                    @endforeach
                @endisset
            </select>
            {{-- Filter Kondisi --}}
            <select name="kondisi" class="w-full border-2 border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:border-sky-500 focus:ring-sky-500 text-sm h-10">
                <option value="">Filter Kondisi</option>
                @php $kondisi_options = ['Baik', 'Rusak', 'Hilang', 'Service']; @endphp
                @foreach($kondisi_options as $kondisi)
                    <option value="{{ $kondisi }}" {{ (request('kondisi') == $kondisi) ? 'selected' : '' }}>{{ $kondisi }}</option>
                @endforeach
            </select>
            {{-- Pencarian Cepat --}}
            <input type="text" name="search" id="search" placeholder="Cari Kode/Nama/SN Aset..." value="{{ request('search') }}"
                   class="col-span-1 md:col-span-2 lg:col-span-2 border-2 border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:border-indigo-500 focus:ring-indigo-500 text-sm h-10">
            {{-- Tombol Aksi Filter --}}
            <div class="col-span-1 flex gap-4">
                 <button type="submit" class="w-1/2 h-10 inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition duration-150">                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                Filter</button>
                 <a href="{{ route('assets.index') }}" class="w-1/2 h-10 inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-100 shadow-sm transition duration-150">Reset</a>
            </div>
        </div>
    </form>
    {{-- AKHIR BLOK FILTER --}}

    {{-- Aksi Massal (Tombol Cetak & Refresh dipindah ke sini, di bawah filter) --}}
    <div class="mb-6">
    <div class="flex items-center gap-3">

        {{-- 1. Tombol Cetak QR Code Terpilih --}}
        <button 
            @click="printSelected" 
            :disabled="selectedAssets.length === 0"
            class="inline-flex items-center gap-2 bg-indigo-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-sm h-10"
            title="Cetak QR Code Aset yang dipilih">
            
            <!-- Icon Printer (Heroicons outline) -->
            <svg xmlns="http://www.w3.org/2000/svg" 
                fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" 
                class="h-5 w-5">
                <path stroke-linecap="round" stroke-linejoin="round" 
                    d="M6 9V2h12v7m-1.5 8h-9M6 9H5a2 2 0 00-2 2v5h4v6h10v-6h4v-5a2 2 0 00-2-2h-1M6 9h12" />
            </svg>

            Cetak QR (<span x-text="selectedAssets.length"></span>)
        </button>

        {{-- 2. Tombol Refresh Pilihan (Hanya Logo) --}}
        <button 
            @click.prevent="clearSelected" 
            :disabled="selectedAssets.length === 0"
            class="inline-flex items-center justify-center bg-white text-gray-700 font-semibold p-2 rounded-lg border border-gray-300 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors h-10 w-10" 
            title="Bersihkan Pilihan Aset">

            <!-- Icon Refresh/Reset (Heroicons outline) -->
            <svg xmlns="http://www.w3.org/2000/svg" 
                fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" 
                class="h-5 w-5">
                <path stroke-linecap="round" stroke-linejoin="round" 
                    d="M4 4v5h.582m0 0A7.5 7.5 0 1112 19.5a7.48 7.48 0 01-6.418-3.418M4.582 9H9" />
            </svg>
        </button>

    </div>
</div>


    {{-- Tabel Aset --}}
    <div class="overflow-x-auto border border-gray-200 rounded-lg">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b-2 border-black">
                <tr class="divide-x divide-gray-300 text-center">
                    {{-- Header Tabel --}}
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
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($assets as $asset)
                <tr class="border-b hover:bg-gray-50 divide-x divide-gray-200 text-center">
                    {{-- Checklist Data --}}
                    <td class="p-4">
                        <input type="checkbox" :value="{{ $asset->id }}" x-model="selectedAssets" @change="saveSelected" class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                    </td>
                    {{-- Data Kolom --}}
                    <td class="px-6 py-4 font-mono font-semibold text-gray-900">{{ $asset->code_asset }}</td>
                    <td class="px-6 py-4 text-left">
                        <div class="font-medium text-gray-900">{{ $asset->nama_barang }}</div>
                        <div class="text-xs text-gray-500">{{ $asset->merk }} @if($asset->tipe)/ {{ $asset->tipe }}@endif</div>
                    </td>
                    <td class="px-6 py-4 text-gray-700">{{ optional($asset->category)->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ optional($asset->subCategory)->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-gray-600 text-left">
                        <div class="font-semibold text-gray-800">{{ optional($asset->assetUser)->nama ?? 'Belum Dialokasikan' }}</div>
                        @if($asset->assetUser)
                            <div class="text-xs text-gray-500">{{ optional(optional($asset->assetUser)->company)->name ?? 'N/A' }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if ($asset->kondisi == 'Baik')
                                bg-green-100 text-green-800
                            @elseif ($asset->kondisi == 'Rusak' || $asset->kondisi == 'Hilang')
                                bg-red-100 text-red-800
                            @else
                                bg-yellow-100 text-yellow-800
                            @endif">
                            {{ $asset->kondisi }}
                        </span>
                    </td>
                    {{-- Kolom Aksi --}}
                    @if(auth()->user()->can('edit-asset') || auth()->user()->can('delete-asset'))
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('assets.show', $asset->id) }}" class="text-emerald-600 hover:text-emerald-900">Lihat</a>
                        @can('edit-asset')
                            <span class="text-gray-400">|</span>
                            <a href="{{ route('assets.edit', $asset->id) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                        @endcan
                        @can('delete-asset')
                            <span class="text-gray-400">|</span>
                            {{-- BARIS YANG DIPERBAIKI: Memastikan ID disisipkan ke fungsi Alpine --}}
                            <a href="#" @click.prevent="deleteAsset({{ $asset->id }})" class="text-red-600 hover:text-red-900">Hapus</a>
                        @endcan
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-10 text-lg font-medium text-gray-500">Tidak ada data aset yang ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINASI --}}
    <div class="mt-6">
        {{ $assets->appends(request()->query())->links() }}
    </div>
    
    {{-- MODAL IMPORT (TETAP) --}}
    <div x-show="showImportModal" x-transition.opacity class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50" x-cloak>
        <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white"
             @click.away="showImportModal = false"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            
            <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Import Data Aset</h3>
            
            {{-- Form Import Data --}}
            <form action="{{ route('assets.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <p class="text-sm text-gray-600 mb-4">
                    Unggah file Excel (*.xlsx atau *.csv) untuk menambahkan data aset secara massal.
                    Pastikan format kolom sudah sesuai.
                </p>
                
                {{-- Input File --}}
                <label for="import_file" class="block text-sm font-medium text-gray-700 mb-2">Pilih File Import</label>
                <input type="file" name="file" id="import_file" required 
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"/>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="showImportModal = false" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md hover:bg-indigo-700 transition">Import Data</button>
                </div>
            </form>
            
        </div>
    </div>
    
</div>

{{-- SCRIPT ALPINE.JS (Fungsionalitas Hapus Data DIVERIFIKASI) --}}
@push('scripts')
<script>
    // --- PERBAIKAN: Mengambil CSRF Token menggunakan Blade/PHP ---
    // Ini lebih terjamin daripada mencari di DOM (document.querySelector)
    const CSRF_TOKEN = '{{ csrf_token() }}';
    
    // Pastikan ID disiapkan untuk pencegahan error
    if (!CSRF_TOKEN) {
        console.error('ERROR KRITIS: Variabel CSRF_TOKEN kosong setelah diisi oleh Blade. Cek kembali layout utama Anda.');
    }

    function assetIndex(initialAssets) {
        return {
            visibleAssets: initialAssets, 
            selectedAssets: JSON.parse(localStorage.getItem('selectedAssets') || '[]'),
            showImportModal: false, 

            saveSelected() {
                localStorage.setItem('selectedAssets', JSON.stringify(this.selectedAssets));
            },

            isSelected(assetId) {
                return this.selectedAssets.includes(assetId);
            },
            
            toggleSelectAll(event) {
                const currentPageAssets = this.visibleAssets; 
                if (event.target.checked) {
                    currentPageAssets.forEach(asset => {
                        if (!this.isSelected(asset.id)) {
                            this.selectedAssets.push(asset.id);
                        }
                    });
                } else {
                    const idsToDeselect = currentPageAssets.map(asset => asset.id);
                    this.selectedAssets = this.selectedAssets.filter(id => !idsToDeselect.includes(id));
                }
                this.saveSelected();
            },

            areAllVisibleSelected() {
                const visibleIds = this.visibleAssets.map(asset => asset.id);
                if (visibleIds.length === 0) return false;
                return visibleIds.every(id => this.isSelected(id));
            },

            clearSelected() {
                if (this.selectedAssets.length === 0) {
                    console.log('Tidak ada aset yang dipilih untuk dibersihkan.');
                    return;
                }
                
                // Konfirmasi opsional (bisa dihilangkan jika terlalu mengganggu)
                {
                    this.selectedAssets = []; // Kosongkan array pilihan
                    this.saveSelected();     // Simpan perubahan ke Local Storage
                    console.log('Semua pilihan aset telah dibersihkan.');
                }
            },
            
            printSelected() {
                if (this.selectedAssets.length === 0) {
                    alert('Mohon pilih minimal satu aset untuk dicetak.');
                    return;
                }
                const query = this.selectedAssets.map(id => `ids[]=${id}`).join('&');
                window.open(`{{ route('assets.print') }}?${query}`, '_blank');
            },

            // --- FUNGSI DELETE ASET YANG SUDAH KONSISTEN ---
            deleteAsset(assetId) {
                // LOG 1: Memastikan ID diterima
                console.log('Fungsi deleteAsset dipanggil. ID Aset yang diterima:', assetId); 
                
                // Pengecekan input
                if (!assetId || typeof assetId !== 'number') {
                    console.error('ERROR: assetId tidak valid atau hilang. Nilai:', assetId);
                    alert('Gagal menghapus: ID aset tidak ditemukan atau format salah.');
                    return;
                }
                
                // Pengecekan token menggunakan variabel global yang baru
                if (!CSRF_TOKEN) {
                    console.error('FATAL ERROR: CSRF Token tidak ditemukan dari variabel global.');
                    alert('Gagal menghapus: CSRF Token hilang. Cek layout atau variabel CSRF_TOKEN.');
                    return;
                }

                if (confirm('Apakah Anda yakin ingin menghapus aset dengan ID ' + assetId + ' ini secara permanen? Tindakan ini tidak dapat dibatalkan.')) {
                    
                    console.log('LOG 2: Konfirmasi diterima. Membuat form POST dengan method DELETE.');
                    
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/assets/${assetId}`; 
                    
                    // Menggunakan variabel global CSRF_TOKEN yang sudah dijamin terisi oleh Blade
                    form.innerHTML = `
                        <input type="hidden" name="_token" value="${CSRF_TOKEN}"> 
                        <input type="hidden" name="_method" value="DELETE">
                    `;
                    document.body.appendChild(form);
                    
                    console.log('LOG 3: Form dibuat. Mengirim request ke URL:', form.action);
                    form.submit();
                } else {
                    console.log('LOG 4: Penghapusan dibatalkan oleh pengguna.');
                }
            }

            
        }
    }
</script>
@endpush
@endsection