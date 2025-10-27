@extends('layouts.app')
@section('title', 'Laporan Inventarisasi Aset')

@section('content')
<div class="bg-white rounded-xl shadow-lg p-6 md:p-8" x-data="{ expandedRows: [] }">
    
    {{-- HEADER DAN TOMBOL AKSI LAPORAN --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-gray-200 pb-6 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Laporan Inventarisasi Aset (Rekapitulasi)</h1>
            <p class="text-sm text-gray-500 mt-1">Klik pada baris rekap untuk melihat detail aset.</p>
        </div>
        
        {{-- Tombol Aksi Laporan (Export PDF/Excel) --}}
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
                        <a href="{{ route('reports.inventory.pdf') }}?{{ request()->getQueryString() }}" target="_blank" class="text-gray-700 block w-full text-left px-4 py-2 text-sm hover:bg-gray-100">
                            Cetak (PDF)
                        </a>
                        <a href="{{ route('reports.inventory.excel') }}?{{ request()->getQueryString() }}" class="text-gray-700 block w-full text-left px-4 py-2 text-sm hover:bg-gray-100">
                            Ekspor Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- FORM FILTER DENGAN ALPINE.JS DINAMIS (Menggunakan layout lama) --}}
    {{-- BLOK FILTER LENGKAP DENGAN LOGIKA SPESIFIKASI DINAMIS --}}
<form method="GET" action="{{ route('reports.inventory') }}" class="mb-6"
    x-data="{ 
        selectedCategory: '{{ request('category_id') ?? '' }}',
        selectedSubCategory: '{{ request('sub_category_id') ?? '' }}',
        
        allSubCategories: {{ $subCategories->mapWithKeys(fn($sc) => [$sc->id => ['name' => $sc->name, 'category_id' => $sc->category_id]])->toJson() }},
        filteredSubCategories: {},
        
        // --- VARIABEL DAN DATA SPESIFIKASI KRUSIAL ---
        allUniqueSpecValues: {{ json_encode($allUniqueSpecValues) }}, 
        selectedSpecKey: '{{ request('spec_key') ?? '' }}', // Nilai ini sudah dipertahankan dari request
        selectedSpecValue: '{{ request('spec_value') ?? '' }}', // Nilai ini sudah dipertahankan dari request
        // ---------------------------------------------
        
        // Filter sub-kategori berdasarkan kategori
        filterSubCategories() {
            this.filteredSubCategories = Object.fromEntries(
                Object.entries(this.allSubCategories).filter(([id, sub]) => !this.selectedCategory || sub.category_id == this.selectedCategory)
            );
            
            // Logika reset hanya untuk Sub-Kategori jika nilai lama tidak lagi valid
            if (this.selectedSubCategory && !this.filteredSubCategories[this.selectedSubCategory]) {
                this.selectedSubCategory = '';
                // Hapus baris reset spesifikasi di sini agar tidak menimpa nilai request
                this.selectedSpecKey = '';
                this.selectedSpecValue = '';
            }
            
            // JIKA SUB-KATEGORI DIPILIH DAN PAGE RELOAD (Filter), KITA TIDAK BOLEH MERESET KEY DAN VALUE
        },
        
        // Fungsi untuk mengembalikan daftar Nilai Spesifikasi unik
        getAvailableSpecValues() {
            const subCatId = this.selectedSubCategory;
            const specKey = this.selectedSpecKey;

            if (subCatId && specKey && this.allUniqueSpecValues[subCatId] && this.allUniqueSpecValues[subCatId][specKey]) {
                return this.allUniqueSpecValues[subCatId][specKey];
            }
            return [];
        },
        
        // Fungsi reset nilai spesifikasi saat kunci (key) spesifikasi berubah
        resetSpecValues() {
            this.selectedSpecValue = ''; // Hanya reset Nilai Spesifikasi jika Kunci Spesifikasi berubah
        }
    }"
    x-init="filterSubCategories()">
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4 p-4 border border-gray-200 rounded-lg bg-gray-50">
        
        {{-- Filter 1: Kategori --}}
        <select name="category_id" x-model="selectedCategory" @change="filterSubCategories" class="w-full border-2 border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:border-sky-500 focus:ring-sky-500 text-sm h-10">
            <option value="">Filter Kategori</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" x-bind:selected="selectedCategory == {{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </select>

        {{-- Filter 2: Sub-Kategori (Dinamis) --}}
        <select name="sub_category_id" x-model="selectedSubCategory" @change="selectedSpecKey = ''; selectedSpecValue = '';" class="w-full border-2 border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:border-sky-500 focus:ring-sky-500 text-sm h-10">
            <option value="">Filter Sub-Kategori</option>
            <template x-for="(sub, id) in filteredSubCategories" :key="id">
                <option :value="id" x-text="sub.name" :selected="id == selectedSubCategory"></option> 
            </template>
        </select>
        
        {{-- Filter 3: Spesifikasi Aset (Pilih BAHAN, RAM, dll) --}}
        <select name="spec_key" x-model="selectedSpecKey" @change="resetSpecValues()" class="w-full border-2 border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:border-sky-500 focus:ring-sky-500 text-sm h-10">
            <option value="">Filter Spesifikasi Aset</option>
            <template x-if="selectedSubCategory && allUniqueSpecValues[selectedSubCategory]">
                {{-- Loop berdasarkan kunci spesifikasi (key) yang tersedia untuk Sub-Kategori yang dipilih --}}
                <template x-for="(values, key) in allUniqueSpecValues[selectedSubCategory]" :key="key">
                    <option :value="key" x-text="key" :selected="key == selectedSpecKey"></option>
                </template>
            </template>
        </select>

        {{-- Filter 4: Nilai Spesifikasi (Pilih 24, 16GB, dll) --}}
        <select name="spec_value" x-model="selectedSpecValue" class="w-full border-2 border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:border-sky-500 focus:ring-sky-500 text-sm h-10">
            <option value="">Pilih Nilai Spesifikasi</option>
            {{-- Loop menggunakan fungsi Alpine.js untuk mendapatkan nilai yang tersedia --}}
            <template x-for="value in getAvailableSpecValues()" :key="value">
                <option :value="value" x-text="value" :selected="value == selectedSpecValue"></option>
            </template>
        </select>
        
        {{-- ... (Filter 5, 6, 7, Pencarian Cepat) ... --}}
        <select name="company_id" class="w-full border-2 border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:border-sky-500 focus:ring-sky-500 text-sm h-10">
            <option value="">Filter Perusahaan</option>
            @foreach($companies as $company)
                <option value="{{ $company->id }}" {{ (request('company_id') == $company->id) ? 'selected' : '' }}>{{ $company->name }}</option>
            @endforeach
        </select>
        
        <select name="asset_user_id" class="w-full border-2 border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:border-sky-500 focus:ring-sky-500 text-sm h-10">
            <option value="">Filter Pengguna Saat Ini</option>
            @isset($assetUsers) 
                @foreach($assetUsers as $user)
                    <option value="{{ $user->id }}" {{ (request('asset_user_id') == $user->id) ? 'selected' : '' }}>{{ $user->nama }}</option>
                @endforeach
            @endisset
        </select>

        <select name="kondisi" class="w-full border-2 border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:border-sky-500 focus:ring-sky-500 text-sm h-10">
            <option value="">Filter Kondisi</option>
            @php
                $kondisi_options = ['Baik', 'Rusak', 'Hilang', 'Service'];
            @endphp
            @foreach($kondisi_options as $kondisi)
                <option value="{{ $kondisi }}" {{ (request('kondisi') == $kondisi) ? 'selected' : '' }}>{{ $kondisi }}</option>
            @endforeach
        </select>
        
        <input type="text" name="search" id="search" placeholder="Cari Kode/Nama/SN Aset..." value="{{ request('search') }}"
               class="col-span-1 border-2 border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:border-indigo-500 focus:ring-indigo-500 text-sm h-10">
        
        {{-- Tombol Aksi Filter --}}
        <div class="col-span-1 flex gap-4">
             <button type="submit" class="w-1/2 h-10 inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>Filter</button>
             <a href="{{ route('reports.inventory') }}" class="w-1/2 h-10 inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-100 shadow-sm transition duration-150">Reset</a>
        </div>
    </div>
</form>
    
    {{-- TABEL LAPORAN (REKAPITULASI) --}}
    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm text-left text-gray-600">
            <thead class="bg-gray-50">
                <tr class="divide-x divide-gray-200">
                    <th class="px-2 py-3 w-8"></th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sub-Kategori / Nama Barang</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perusahaan Aset</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kondisi</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Nilai (Rp)</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($inventorySummary as $key => $summary)
                    @php $rowId = $loop->index; @endphp 
                    
                    {{-- Baris Rekapitulasi (MASTER) --}}
                    <tr class="hover:bg-gray-100 cursor-pointer divide-x divide-gray-200"
                        @click="
                            const index = expandedRows.indexOf({{ $rowId }});
                            if (index > -1) {
                                expandedRows.splice(index, 1);
                            } else {
                                expandedRows.push({{ $rowId }});
                            }
                        "
                    >
                        <td class="px-2 py-4 text-center">
                            <svg class="w-5 h-5 text-gray-500 transform transition-transform duration-200" :class="{ 'rotate-180': expandedRows.includes({{ $rowId }}) }" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $summary['category_name'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $summary['sub_category_display_name'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $summary['company_name'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                  @if ($summary['kondisi'] == 'Baik') bg-green-100 text-green-800
                                  @elseif ($summary['kondisi'] == 'Rusak') bg-red-100 text-red-800
                                  @else bg-yellow-100 text-yellow-800 @endif">
                                {{ $summary['kondisi'] }}
                            </span>
                        </td>
                        {{-- Menggunakan key 'count' dari ReportController --}}
                        <td class="px-6 py-4 whitespace-nowrap text-center font-bold">{{ number_format($summary['count'], 0, ',', '.') }}</td>
                        {{-- Menggunakan key 'total_harga' dari ReportController --}}
                        <td class="px-6 py-4 whitespace-nowrap text-right font-bold">{{ number_format($summary['total_harga'], 0, ',', '.') }}</td>
                    </tr>

                    {{-- Baris Detail yang Tersembunyi --}}
                    <tr x-show="expandedRows.includes({{ $rowId }})" x-cloak x-transition>
                        <td colspan="7" class="px-6 py-4 bg-gray-50 border-b border-gray-300"> 
                            
                            {{-- PENAMBAHAN TANDA/JUDUL INI --}}
                            <div class="mb-3 text-sm font-semibold text-gray-700 border-b border-gray-200 pb-2 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                </svg>
                                Detail Aset untuk {{ $summary['sub_category_display_name'] }} (Total: {{ $summary['count'] }} Unit)
                            </div>

                            <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-sm">
                                {{-- Tabel Detail --}}
                                <table class="min-w-full text-xs divide-y divide-gray-200">
                                    <thead class="bg-gray-200">
                                        <tr class="divide-x divide-gray-200"> 
                                            <th class="px-4 py-2 text-left font-semibold">Nama Barang</th>
                                            <th class="px-4 py-2 text-left font-semibold">Pengguna Saat Ini</th>
                                            <th class="px-4 py-2 text-left font-semibold">Perusahaan Pemilik Aset</th>
                                            <th class="px-4 py-2 text-right font-semibold">Harga (Rp)</th>
                                            <th class="px-4 py-2 text-center font-semibold w-24">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach($summary['details'] as $detail)
                                        <tr class="divide-x divide-gray-200">
                                            <td class="px-4 py-2">{{ $detail->nama_barang }} ({{ $detail->code_asset }})</td>
                                            <td class="px-4 py-2">
                                                @if($detail->assetUser)
                                                    <div class="font-semibold">{{ $detail->assetUser->nama }}</div>
                                                    <div class="text-gray-500 text-[11px] font-medium">{{ optional($detail->assetUser->company)->name ?? 'N/A' }}</div>
                                                @else
                                                    Stok (Tidak Dialokasikan)
                                                @endif
                                            </td>
                                            <td class="px-4 py-2">{{ optional($detail->company)->name ?? 'N/A' }}</td>
                                            <td class="px-4 py-2 text-right">{{ number_format($detail->harga_total, 0, ',', '.') }}</td>
                                            <td class="px-4 py-2 text-center">
                                                <a href="{{ route('assets.show', $detail->id) }}" class="text-xs font-semibold text-sky-600 hover:text-sky-800 hover:underline whitespace-nowrap">Lihat Data</a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-10 text-lg font-medium text-gray-500">Tidak ada data inventaris yang ditemukan dengan filter ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection