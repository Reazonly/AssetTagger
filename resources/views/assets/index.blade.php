@extends('layouts.app')
@section('title', 'Daftar Aset')
@section('content')
<div class="bg-white rounded-xl shadow-lg p-6 md:p-8">

    {{-- Header: Judul dan Tombol Aksi Utama --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-gray-200 pb-6 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Daftar Aset</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola, cari, dan filter semua aset perusahaan.</p>
        </div>
        <div class="flex items-center gap-3 mt-4 md:mt-0">
            <button onclick="document.getElementById('importModal').classList.remove('hidden')" class="inline-flex items-center gap-2 bg-white text-gray-700 font-semibold px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                Import
            </button>
            <a href="{{ route('assets.create') }}" class="inline-flex items-center gap-2 bg-emerald-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-emerald-700 transition-colors shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Aset
            </a>
        </div>
    </div>

    {{-- Filter, Pencarian, dan Aksi Pilihan --}}
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
            <button id="exportFilteredBtn" class="flex items-center gap-2 bg-green-700 text-white font-semibold px-4 py-2 rounded-lg transition-colors">
                Export Hasil Filter
            </button>
        </div>
    </div>

    {{-- Tabel Aset --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                <tr>
                    <th scope="col" class="p-4"><input type="checkbox" id="selectAllCheckbox" class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"></th>
                    <th scope="col" class="px-6 py-3">Kode Aset</th>
                    <th scope="col" class="px-6 py-3">Nama Barang</th>
                    <th scope="col" class="px-6 py-3">Kategori</th>
                    <th scope="col" class="px-6 py-3">Pengguna</th>
                    <th scope="col" class="px-6 py-3">Kondisi</th>
                    <th scope="col" class="px-6 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white">
                @forelse ($assets as $asset)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="w-4 p-4"><input type="checkbox" name="asset_ids[]" value="{{ $asset->id }}" class="asset-checkbox h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"></td>
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">{{ $asset->code_asset }}</td>
                        <td class="px-6 py-4">{{ $asset->nama_barang }}</td>
                        <td class="px-6 py-4">{{ optional($asset->category)->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4">{{ optional($asset->user)->nama_pengguna ?? 'N/A' }}</td>
                        <td class="px-6 py-4">
                            @if($asset->kondisi == 'BAIK')
                                <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">BAIK</span>
                            @elseif($asset->kondisi == 'RUSAK')
                                <span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">RUSAK</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded-full">PERBAIKAN</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <a href="{{ route('assets.show', $asset->id) }}" class="font-medium text-emerald-600 hover:text-emerald-800">Lihat</a>
                            <a href="{{ route('assets.edit', $asset->id) }}" class="font-medium text-blue-600 hover:text-blue-800 ml-4">Edit</a>
                            <form action="{{ route('assets.destroy', $asset->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aset ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-medium text-red-600 hover:text-red-800 ml-4">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-10 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Aset tidak ditemukan</h3>
                            <p class="mt-1 text-sm text-gray-500">Coba ubah filter atau kata kunci pencarian Anda.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Pagination --}}
    <div class="mt-6">{{ $assets->appends(request()->query())->links() }}</div>
</div>

{{-- Modal Impor --}}
<div id="importModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 overflow-y-auto h-full w-full hidden z-50 transition-opacity">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-xl bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-lg font-medium text-gray-900">Import Data dari Excel</h3>
            <button onclick="document.getElementById('importModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <form action="{{ route('assets.import') }}" method="POST" enctype="multipart/form-data" class="mt-4">
            @csrf
            <p class="text-sm text-gray-600 mb-4">Pilih file Excel (.xlsx) atau CSV (.csv) untuk mengimpor data aset secara massal.</p>
            <input type="file" name="file" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100" required>
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">Batal</button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Upload & Import</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const assetCheckboxes = document.querySelectorAll('.asset-checkbox');
    const printSelectedBtn = document.getElementById('printSelectedBtn');
    const exportSelectedBtn = document.getElementById('exportSelectedBtn');
    const exportFilteredBtn = document.getElementById('exportFilteredBtn');

    function updateActionButtonsState() {
        const selectedCount = Array.from(assetCheckboxes).filter(cb => cb.checked).length;
        const anyChecked = selectedCount > 0;

        printSelectedBtn.disabled = !anyChecked;
        exportSelectedBtn.disabled = !anyChecked;
    }

    selectAllCheckbox.addEventListener('change', function () {
        assetCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateActionButtonsState();
    });

    assetCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            if (!this.checked) {
                selectAllCheckbox.checked = false;
            } else {
                const allChecked = Array.from(assetCheckboxes).every(cb => cb.checked);
                selectAllCheckbox.checked = allChecked;
            }
            updateActionButtonsState();
        });
    });

    printSelectedBtn.addEventListener('click', function () {
        const selectedIds = Array.from(assetCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => 'ids[]=' + cb.value)
            .join('&');
        if (selectedIds) {
            window.open("{{ route('assets.print') }}?" + selectedIds, '_blank');
        }
    });

    // --- PERBAIKAN LOGIKA EKSPOR ---

    // 1. Ekspor item yang dipilih saja
    exportSelectedBtn.addEventListener('click', function() {
        const selectedIds = Array.from(assetCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => 'ids[]=' + cb.value)
            .join('&');
        
        if (selectedIds) {
            // Kita tetap sertakan filter kategori jika ada, agar nama file dan kolomnya sesuai
            const categoryId = document.getElementById('category_filter').value;
            let exportUrl = "{{ route('assets.export') }}?" + selectedIds;
            if (categoryId) {
                exportUrl += `&category_id=${categoryId}`;
            }
            window.location.href = exportUrl;
        }
    });

    // 2. Ekspor semua hasil yang sedang difilter (tidak peduli dicentang atau tidak)
    exportFilteredBtn.addEventListener('click', function() {
        const categoryId = document.getElementById('category_filter').value;
        const searchTerm = document.querySelector('input[name="search"]').value;
        
        const params = new URLSearchParams();
        if (categoryId) {
            params.append('category_id', categoryId);
        }
        if (searchTerm) {
            params.append('search', searchTerm);
        }

        window.location.href = "{{ route('assets.export') }}?" + params.toString();
    });

    // Initial check
    updateActionButtonsState();
});
</script>
@endpush
