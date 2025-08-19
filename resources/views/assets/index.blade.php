@extends('layouts.app')

@section('title', 'Daftar Aset')

@section('content')
{{-- PERBAIKAN: Menambahkan `fileName` ke state Alpine.js untuk input file --}}
<div x-data="{ showImportModal: false, fileName: '' }" class="bg-white rounded-xl shadow-lg p-6 md:p-8">

    {{-- Header Halaman --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-gray-200 pb-6 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Daftar Aset</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola, cari, dan filter semua aset perusahaan.</p>
        </div>
        <div class="flex items-center gap-3 mt-4 md:mt-0">
            @can('import-asset')
            <button @click="showImportModal = true" class="inline-flex items-center gap-2 bg-white text-gray-700 font-semibold px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                Import
            </button>
            @endcan
            @can('create-asset')
            <a href="{{ route('assets.create') }}" class="inline-flex items-center gap-2 bg-emerald-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-emerald-700 transition-colors shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                Tambah Aset
            </a>
            @endcan
        </div>
    </div>

    
    {{-- Filter, Pencarian, dan Tombol Aksi --}}
    <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-4">
        <form action="{{ route('assets.index') }}" method="GET" class="w-full md:w-auto flex flex-col sm:flex-row items-center gap-3">
            <div class="relative w-full sm:w-64">
                <input type="text" name="search" placeholder="Cari kode, nama, S/N..." value="{{ request('search') }}" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
            <div class="w-full sm:w-auto">
                <select name="category_id" id="category_filter" onchange="this.form.submit()" class="w-full border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
        <div class="flex items-center gap-2">
            <button id="printSelectedBtn" disabled class="flex items-center gap-2 bg-blue-500 text-white font-semibold px-4 py-2 rounded-lg transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed">
                Cetak Terpilih
            </button>
            <button id="exportSelectedBtn" disabled class="flex items-center gap-2 bg-green-600 text-white font-semibold px-4 py-2 rounded-lg transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed">
                Export Terpilih
            </button>
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" type="button" class="inline-flex items-center gap-2 px-4 py-2 text-gray-600 font-semibold rounded-lg hover:bg-gray-100 border text-sm">
                    <span>Export Filter</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                </button>
                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10" x-cloak>
                    <div class="py-1" role="menu" aria-orientation="vertical">
                         @foreach($categories as $category)
                            <a href="{{ route('assets.export', array_merge(request()->query(), ['category_id_export' => $category->id])) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Export {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Aset --}}
    <div class="overflow-x-auto border border-gray-200 rounded-lg">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b-2 border-black">
                <tr class="divide-x divide-gray-300 text-center">
                    <th scope="col" class="p-4"><input type="checkbox" id="selectAllCheckbox" class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"></th>
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
                @forelse ($assets as $asset)
                    <tr class="border-b hover:bg-gray-50 divide-x divide-gray-200 text-center">
                        <td class="w-4 p-4"><input type="checkbox" name="asset_ids[]" value="{{ $asset->id }}" class="asset-checkbox h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"></td>
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">{{ $asset->code_asset }}</td>
                        <td class="px-6 py-4">{{ $asset->nama_barang }}</td>
                        <td class="px-6 py-4">{{ optional($asset->category)->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4">{{ optional($asset->subCategory)->name ?? '-' }}</td>
                        <td class="px-6 py-4">{{ optional($asset->assetUser)->nama ?? 'N/A' }}</td>
                        <td class="px-6 py-4"><span class="px-2 py-1 text-xs font-semibold rounded-full @if($asset->kondisi == 'Baik') bg-green-100 text-green-800 @elseif($asset->kondisi == 'Rusak') bg-red-100 text-red-800 @else bg-yellow-100 text-yellow-800 @endif">{{ $asset->kondisi }}</span></td>
                        @if(auth()->user()->can('edit-asset') || auth()->user()->can('delete-asset'))
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            @can('view-asset')
                                <a href="{{ route('assets.show', $asset->id) }}" class="font-medium text-emerald-600 hover:text-emerald-800">Lihat</a>
                            @endcan
                            @can('edit-asset')
                                <a href="{{ route('assets.edit', $asset->id) }}" class="font-medium text-blue-600 hover:text-blue-800 ml-4">Edit</a>
                            @endcan
                            @can('delete-asset')
                                <form action="{{ route('assets.destroy', $asset->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aset ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-medium text-red-600 hover:text-red-800 ml-4">Hapus</button>
                                </form>
                            @endcan
                        </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->can('edit-asset') || auth()->user()->can('delete-asset') ? '8' : '7' }}" class="text-center py-10 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Aset tidak ditemukan</h3>
                            <p class="mt-1 text-sm text-gray-500">Coba ubah filter atau kata kunci pencarian Anda.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="mt-6">{{ $assets->appends(request()->query())->links() }}</div>

    {{-- ===================================================================== --}}
    {{-- PERBAIKAN MODAL IMPORT DI SINI --}}
    {{-- ===================================================================== --}}
    @can('import-asset')
        <div x-show="showImportModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div @click.away="showImportModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Import Data Aset</h3>
                <form action="{{ route('assets.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <p class="text-sm text-gray-600 mb-4">
                        Unggah file Excel. Pastikan kolom header sesuai template export, contohnya: <strong>Nama Barang, Kategori, Sub Kategori, Serial Number, Pengguna Aset, Kondisi, Lokasi.</strong>
                    </p>
                    <div class="mt-4">
                        {{-- Input file yang disembunyikan --}}
                        <input
                            type="file"
                            name="file"
                            id="file-upload"
                            class="hidden"
                            @change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''"
                            required
                        >
                        {{-- Desain tombol dan teks nama file --}}
                        <div class="flex items-center">
                            <label for="file-upload" class="cursor-pointer inline-flex items-center px-4 py-2 bg-emerald-50 text-emerald-700 font-semibold text-sm rounded-lg hover:bg-emerald-100">
                                Choose File
                            </label>
                            <span x-text="fileName || 'No file chosen'" class="ml-4 text-sm text-gray-500 truncate"></span>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="showImportModal = false" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">Batal</button>
                        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-md hover:bg-emerald-700">Import</button>
                    </div>
                </form>
            </div>
        </div>
    @endcan
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const storageKey = 'selectedAssetIds';
    
    if (window.location.search === '') {
        localStorage.removeItem(storageKey);
    }

    let selectedAssetIds = JSON.parse(localStorage.getItem(storageKey)) || [];

    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const assetCheckboxes = document.querySelectorAll('.asset-checkbox');
    const printSelectedBtn = document.getElementById('printSelectedBtn');
    const exportSelectedBtn = document.getElementById('exportSelectedBtn');
    
    const saveState = () => {
        localStorage.setItem(storageKey, JSON.stringify(selectedAssetIds));
    };

    const updateUI = () => {
        const anyChecked = selectedAssetIds.length > 0;
        
        if(printSelectedBtn) printSelectedBtn.disabled = !anyChecked;
        if(exportSelectedBtn) exportSelectedBtn.disabled = !anyChecked;

        assetCheckboxes.forEach(checkbox => {
            const assetId = parseInt(checkbox.value, 10);
            checkbox.checked = selectedAssetIds.includes(assetId);
        });

        const allVisibleCheckboxes = document.querySelectorAll('.asset-checkbox');
        const allVisibleChecked = allVisibleCheckboxes.length > 0 && Array.from(allVisibleCheckboxes).every(cb => cb.checked);
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = allVisibleChecked;
        }
    };

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            const isChecked = this.checked;
            document.querySelectorAll('.asset-checkbox').forEach(checkbox => {
                const assetId = parseInt(checkbox.value, 10);
                const index = selectedAssetIds.indexOf(assetId);
                if (isChecked) {
                    checkbox.checked = true;
                    if (index === -1) {
                        selectedAssetIds.push(assetId);
                    }
                } else {
                    checkbox.checked = false;
                    if (index > -1) {
                        selectedAssetIds.splice(index, 1);
                    }
                }
            });
            saveState();
            updateUI();
        });
    }

    assetCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const assetId = parseInt(this.value, 10);
            const index = selectedAssetIds.indexOf(assetId);
            if (this.checked && index === -1) {
                selectedAssetIds.push(assetId);
            } else if (!this.checked && index > -1) {
                selectedAssetIds.splice(index, 1);
            }
            saveState();
            updateUI();
        });
    });

    if (printSelectedBtn) {
        printSelectedBtn.addEventListener('click', function () {
            if (selectedAssetIds.length > 0) {
                const queryParams = selectedAssetIds.map(id => 'ids[]=' + id).join('&');
                window.open("{{ route('assets.print') }}?" + queryParams, '_blank');
            }
        });
    }

    if (exportSelectedBtn) {
        exportSelectedBtn.addEventListener('click', function() {
            if (selectedAssetIds.length > 0) {
                const queryParams = selectedAssetIds.map(id => 'ids[]=' + id).join('&');
                window.location.href = "{{ route('assets.export') }}?" + queryParams;
            }
        });
    }
    
    updateUI();
});
</script>
@endpush
