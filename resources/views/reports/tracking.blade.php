@extends('layouts.app')
@section('title', 'Laporan Alokasi Aset Saat Ini')

@section('content')

{{-- x-data untuk mengelola expanded rows dan filter sub-category --}}
<div class="bg-white rounded-xl shadow-lg p-6 md:p-8" 
     x-data="{ 
         expandedRows: [],
         selectedCategory: '{{ request('category_id') ?? '' }}',
         selectedSubCategory: '{{ request('sub_category_id') ?? '' }}',
         
         // Data sub-categories harus disediakan dari controller ke view
         allSubCategories: {{ $subCategories->mapWithKeys(fn($sc) => [$sc->id => ['name' => $sc->name, 'category_id' => $sc->category_id]])->toJson() }},
         filteredSubCategories: {},
         
         filterSubCategories() {
             this.filteredSubCategories = Object.fromEntries(
                 Object.entries(this.allSubCategories).filter(([id, sub]) => !this.selectedCategory || sub.category_id == this.selectedCategory)
             );
             // Reset sub-category jika kategori berubah dan sub-category lama tidak ada
             if (this.selectedSubCategory && !this.filteredSubCategories[this.selectedSubCategory]) {
                 this.selectedSubCategory = '';
             }
         }
     }"
     x-init="filterSubCategories()">
    
    {{-- HEADER DAN TOMBOL AKSI --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-gray-200 pb-6 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Laporan Alokasi Aset Saat Ini</h1>
            <p class="text-sm text-gray-500 mt-1">Daftar aset yang sedang dialokasikan kepada pengguna.</p>
        </div>
        <div class="flex items-center gap-3 mt-4 md:mt-0">
            <div class="flex items-center gap-3 mt-4 md:mt-0">
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="inline-flex items-center gap-2 bg-sky-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:bg-sky-700 transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 h-10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                    Aksi Laporan
                </button>
                <div x-show="open" @click.outside="open = false" 
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu">
                    <div class="py-1" role="none">
                        {{-- Menggunakan route yang benar untuk Laporan Inventaris --}}
                        <a href="{{ route('reports.tracking.pdf') }}?{{ request()->getQueryString() }}" target="_blank" class="text-gray-700 block w-full text-left px-4 py-2 text-sm hover:bg-gray-100">
                            Cetak (PDF)
                        </a>
                        <a href="{{ route('reports.tracking.excel') }}?{{ request()->getQueryString() }}" class="text-gray-700 block w-full text-left px-4 py-2 text-sm hover:bg-gray-100">
                            Ekspor Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
    
    {{-- KOTAK FILTER --}}
    <form method="GET" action="{{ route('reports.tracking') }}" class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4 p-4 border border-gray-200 rounded-lg bg-gray-50">
            
            {{-- Filter 1: Kategori --}}
            <select name="category_id" x-model="selectedCategory" @change="filterSubCategories" class="w-full border-2 border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:border-sky-500 focus:ring-sky-500 text-sm h-10">
                <option value="">Filter Kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" :selected="selectedCategory == {{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>

            {{-- Filter 2: Sub-Kategori (Dinamis) --}}
            <select name="sub_category_id" x-model="selectedSubCategory" class="w-full border-2 border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:border-sky-500 focus:ring-sky-500 text-sm h-10">
                <option value="">Filter Sub-Kategori</option>
                {{-- Gunakan template Alpine untuk filter sub-category --}}
                <template x-for="(sub, id) in filteredSubCategories" :key="id">
                    <option :value="id" :selected="id == selectedSubCategory" x-text="sub.name"></option> 
                </template>
            </select>
            
            {{-- Filter 3: Perusahaan --}}
            <select name="company_id" class="w-full border-2 border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:border-sky-500 focus:ring-sky-500 text-sm h-10">
                <option value="">Filter Perusahaan</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}" {{ (request('company_id') == $company->id) ? 'selected' : '' }}>{{ $company->name }}</option>
                @endforeach
            </select>
            
            {{-- Filter 4: Lokasi --}}
            <select name="location" class="w-full border-2 border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:border-sky-500 focus:ring-sky-500 text-sm h-10">
                <option value="">Filter Lokasi</option>
                @isset($locations)
                    @foreach($locations as $location)
                        <option value="{{ $location }}" {{ (request('location') == $location) ? 'selected' : '' }}>{{ $location }}</option>
                    @endforeach
                @endisset
            </select>

            {{-- Filter 5: Pengguna (Baru ditambahkan) --}}
            <select name="asset_user_id" class="w-full border-2 border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:border-sky-500 focus:ring-sky-500 text-sm h-10">
                <option value="">Filter Pengguna Saat Ini</option>
                {{-- Asumsi Anda memiliki variabel $assetUsers dari controller --}}
                @isset($assetUsers) 
                    @foreach($assetUsers as $user)
                        <option value="{{ $user->id }}" {{ (request('asset_user_id') == $user->id) ? 'selected' : '' }}>{{ $user->nama }}</option>
                    @endforeach
                @endisset
            </select>
            
            {{-- Pencarian Cepat --}}
            <input type="text" name="search" id="search" placeholder="Cari Kode/Nama/SN Aset..." value="{{ request('search') }}"
                   class="col-span-1 md:col-span-2 lg:col-span-2 border-2 border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:border-indigo-500 focus:ring-indigo-500 text-sm h-10">
            
            {{-- Tombol Aksi Filter --}}
            <div class="col-span-1 flex gap-4">
                 <button type="submit" class="w-1/2 h-10 inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition duration-150">                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    Filter</button>
                 <a href="{{ route('reports.tracking') }}" class="w-1/2 h-10 inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-100 shadow-sm transition duration-150">Reset</a>
            </div>
        </div>
    </form>
    
    {{-- TABEL LAPORAN --}}
    <div class="overflow-x-auto border border-gray-300 rounded-lg shadow-sm">
        <table class="min-w-full border border-gray-200 text-sm text-left text-gray-600">
            <thead class="bg-gray-100 border-b border-gray-300">
                <tr class="divide-x divide-gray-300"> 
                    {{-- Judul kolom tetap --}}
                    <th class="w-10 px-2 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider border-r border-gray-300"></th> {{-- Toggle Expand --}}
                    <th class="w-10 px-2 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider border-r border-gray-300">No.</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider border-r border-gray-300">Kode & Nama Barang Aset</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider border-r border-gray-300">Kategori & Sub-Kategori</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider border-r border-gray-300">Pengguna Saat Ini / Detail</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider border-r border-gray-300">Lokasi Fisik</th>
                    <th class="w-20 px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider border-r border-gray-300">Kondisi</th>
                    <th class="w-24 px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($assets as $asset)
                    {{-- BARIS UTAMA DIBUAT CLICKABLE --}}
                    <tr class="hover:bg-gray-50 divide-x divide-gray-200 cursor-pointer"
                        {{-- Pindahkan logika expand ke sini --}}
                        x-on:click.prevent="expandedRows.includes({{ $asset->id }}) ? expandedRows = expandedRows.filter(id => id !== {{ $asset->id }}) : expandedRows.push({{ $asset->id }})"> 
                        
                        {{-- TOGGLE EXPAND (Hanya Icon Panah yang Berputar) --}}
                        <td class="px-2 py-2 text-center border-r border-gray-200">
                            <svg class="w-5 h-5 text-gray-500 transform transition-transform duration-200" 
                                 :class="{ 'rotate-180': expandedRows.includes({{ $asset->id }}) }" 
                                 fill="none" 
                                 viewBox="0 0 24 24" 
                                 stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </td>

                        {{-- NO --}}
                        <td class="px-2 py-2 text-center whitespace-nowrap border-r border-gray-200">{{ $loop->iteration }}</td>
                        
                        {{-- KODE & NAMA BARANG (Kode Aset diubah menjadi text-gray-900) --}}
                        <td class="px-4 py-2 border-r border-gray-200">
                            <div class="font-mono text-gray-900 font-semibold">{{ $asset->code_asset }}</div> 
                            <div class="text-gray-900 font-medium">{{ $asset->nama_barang }}</div>
                        </td>
                        
                        {{-- KATEGORI & SUB-KATEGORI --}}
                        <td class="px-4 py-2 border-r border-gray-200">
                             <div class="text-gray-700">{{ optional($asset->category)->name ?? 'N/A' }}</div>
                             <div class="text-gray-500 text-xs">{{ optional($asset->subCategory)->name ?? 'N/A' }}</div>
                        </td>

                        {{-- PENGGUNA & DETAIL --}}
                        <td class="px-4 py-2 border-r border-gray-200">
                             <div class="font-semibold text-gray-800">{{ optional($asset->assetUser)->nama ?? 'TIDAK DIALOKASIKAN' }}</div>
                             @if($asset->assetUser)
                                 <div class="text-gray-700 text-xs">{{ optional($asset->assetUser)->jabatan ?? 'N/A' }}</div>
                                 <div class="text-gray-500 text-xs font-medium">{{ optional(optional($asset->assetUser)->company)->name ?? 'N/A' }}</div>
                             @endif
                        </td>
                        
                        {{-- LOKASI FISIK --}}
                        <td class="px-4 py-2 whitespace-nowrap text-gray-500 border-r border-gray-200">
                            {{ $asset->lokasi ?? 'N/A' }} 
                        </td>

                        {{-- KONDISI --}}
                        <td class="px-4 py-2 whitespace-nowrap text-center border-r border-gray-200">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                  @if ($asset->kondisi == 'Baik')
                                      bg-green-100 text-green-800
                                  @elseif ($asset->kondisi == 'Rusak')
                                      bg-red-100 text-red-800
                                  @else
                                      bg-yellow-100 text-yellow-800
                                  @endif
                                  ">
                                {{ $asset->kondisi }}
                            </span>
                        </td>
                        
                        {{-- AKSI (Tambahkan x-on:click.stop agar klik di sini tidak expand/collapse baris) --}}
                        <td class="px-4 py-2 text-center" x-on:click.stop>
                            <a href="{{ route('assets.show', $asset->id) }}" class="text-xs font-semibold text-sky-600 hover:text-sky-800 hover:underline whitespace-nowrap">Lihat Data</a>
                        </td>
                    </tr>
                    
                    {{-- BARIS DETAIL/EXPAND (RIWAYAT PENGGUNA) --}}
                    {{-- TRANSISI DISAMAKAN DENGAN INVENTARIS: max-h --}}
                    <tr x-show="expandedRows.includes({{ $asset->id }})" 
                        x-transition:enter="transition ease-out duration-300" 
                        x-transition:enter-start="opacity-0 max-h-0" 
                        x-transition:enter-end="opacity-100 max-h-96" 
                        x-transition:leave="transition ease-in duration-300" 
                        x-transition:leave-start="opacity-100 max-h-96" 
                        x-transition:leave-end="opacity-0 max-h-0" 
                        class="bg-gray-50">
                        <td colspan="8" class="px-6 py-4 border-t border-gray-300">
                             <div class="text-sm font-bold text-gray-800 mb-3">Riwayat Pengguna ({{ $asset->history->count() ?? 0 }})</div>
                             <div class="overflow-y-auto max-h-48 space-y-3">
                                 @forelse($asset->history->sortByDesc('tanggal_mulai') as $h)
                                     <div class="p-3 bg-white border border-gray-200 rounded-lg shadow-sm flex justify-between items-start">
                                         
                                         {{-- Kiri: Detail Pengguna --}}
                                         <div>
                                             <div class="font-semibold text-gray-900">{{ optional($h->assetUser)->nama ?? 'N/A' }}</div>
                                             <div class="text-xs text-gray-700">{{ optional($h->assetUser)->jabatan ?? 'N/A' }}</div>
                                             <div class="text-xs text-gray-500 font-medium">{{ optional(optional($h->assetUser)->company)->name ?? 'N/A' }}</div>
                                         </div>
                                         
                                         {{-- Kanan: Detail Tanggal (Dibuat lebih rapi dalam dua baris) --}}
                                         <div class="text-right flex-shrink-0 text-xs">
                                             <p class="text-gray-600 font-medium whitespace-nowrap">
                                                 Mulai: {{ \Carbon\Carbon::parse($h->tanggal_mulai)->format('d M Y') }}
                                             </p>
                                             <p class="text-gray-600 font-medium whitespace-nowrap mt-1">
                                                 Selesai: 
                                                 @if(is_null($h->tanggal_selesai))
                                                     <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded font-medium">Saat Ini</span>
                                                 @else
                                                     {{ \Carbon\Carbon::parse($h->tanggal_selesai)->format('d M Y') }}
                                                 @endif
                                             </p>
                                         </div>
                                     </div>
                                 @empty
                                     <p class="text-center text-gray-500 py-2">Tidak ada riwayat pengguna untuk aset ini.</p>
                                 @endforelse
                             </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-10 text-lg font-medium text-gray-500">
                            Tidak ada data aset yang sedang dialokasikan saat ini, atau tidak ada yang cocok dengan filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- PAGINASI --}}
    <div class="mt-6">
        {{ $assets->links() }}
    </div>

</div>
@endsection