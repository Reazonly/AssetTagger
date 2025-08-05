@extends('layouts.public')
@section('title', 'Detail Aset - ' . $asset->code_asset)
@section('content')
    <div class="border-b pb-4 mb-8">
        <h1 class="text-3xl font-bold text-gray-800">{{ $asset->nama_barang }}</h1>
        <p class="text-lg text-emerald-600 font-mono tracking-wider">{{ $asset->code_asset }}</p>
    </div>

    <div class="space-y-6">
        {{-- Informasi Umum --}}
        <div class="bg-white p-6 rounded-xl border">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 border-b pb-3">Informasi Umum</h3>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5 text-sm">
                <div class="flex flex-col"><dt class="font-medium text-gray-500">Kategori</dt><dd class="text-gray-900 mt-1">{{ optional($asset->category)->name ?? 'N/A' }}</dd></div>
                
                {{-- PERBAIKAN: Menggunakan relasi subCategory untuk menampilkan nama, bukan ID --}}
                <div class="flex flex-col"><dt class="font-medium text-gray-500">Sub Kategori</dt><dd class="text-gray-900 mt-1">{{ optional($asset->subCategory)->name ?? 'N/A' }}</dd></div>
                
                <div class="flex flex-col"><dt class="font-medium text-gray-500">Perusahaan</dt><dd class="text-gray-900 mt-1">{{ optional($asset->company)->name ?? 'N/A' }}</dd></div>
                
                @if(optional($asset->category)->requires_merk)
                    <div class="flex flex-col"><dt class="font-medium text-gray-500">Merk</dt><dd class="text-gray-900 mt-1">{{ $asset->merk ?? 'N/A' }}</dd></div>
                @else
                    <div class="flex flex-col"><dt class="font-medium text-gray-500">Tipe</dt><dd class="text-gray-900 mt-1">{{ $asset->tipe ?? 'N/A' }}</dd></div>
                @endif

                @if($asset->serial_number)
                    <div class="flex flex-col"><dt class="font-medium text-gray-500">Serial Number</dt><dd class="text-gray-900 mt-1">{{ $asset->serial_number }}</dd></div>
                @endif
                @if($asset->kondisi)
                    <div class="flex flex-col"><dt class="font-medium text-gray-500">Kondisi</dt><dd class="text-gray-900 mt-1">{{ $asset->kondisi }}</dd></div>
                @endif
                @if($asset->lokasi)
                    <div class="flex flex-col"><dt class="font-medium text-gray-500">Lokasi Fisik</dt><dd class="text-gray-900 mt-1">{{ $asset->lokasi }}</dd></div>
                @endif
                
                <div class="sm:col-span-2 mt-4 pt-5 border-t">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pengguna</h4>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5 text-sm">
                        <div class="flex flex-col"><dt class="font-medium text-gray-500">Pengguna Saat Ini</dt><dd class="text-gray-900 mt-1">{{ optional($asset->user)->nama_pengguna ?? 'Tidak ada' }}</dd></div>
                        @if(optional($asset->user)->jabatan)
                            <div class="flex flex-col"><dt class="font-medium text-gray-500">Jabatan</dt><dd class="text-gray-900 mt-1">{{ $asset->user->jabatan }}</dd></div>
                        @endif
                    </dl>
                </div>
            </dl>
        </div>
        {{-- Spesifikasi Teknis --}}
        <div class="bg-white p-6 rounded-xl border">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 border-b pb-3">Spesifikasi & Deskripsi</h3>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5 text-sm">
                @forelse($asset->specifications ?? [] as $key => $value)
                    <div class="flex flex-col">
                        <dt class="font-medium text-gray-500">{{ Str::title(str_replace('_', ' ', $key)) }}</dt>
                        <dd class="text-gray-900 mt-1">{{ $value ?? 'N/A' }}</dd>
                    </div>
                @empty
                    <p class="text-gray-500 sm:col-span-2">Tidak ada detail spesifikasi yang diberikan.</p>
                @endforelse
            </dl>
        </div>

        {{-- Informasi Pembelian & Dokumen --}}
        <div class="bg-white p-6 rounded-xl border">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 border-b pb-3">Informasi Pembelian & Dokumen</h3>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5 text-sm">
                <div class="flex flex-col"><dt class="font-medium text-gray-500">Tanggal Beli</dt><dd class="text-gray-900 mt-1">{{ optional($asset->tanggal_pembelian)->isoFormat('D MMMM YYYY') ?? 'N/A' }}</dd></div>
                <div class="flex flex-col"><dt class="font-medium text-gray-500">Harga</dt><dd class="text-gray-900 mt-1">Rp {{ number_format($asset->harga_total ?? 0, 0, ',', '.') }}</dd></div>
                <div class="flex flex-col"><dt class="font-medium text-gray-500">Nomor PO</dt><dd class="text-gray-900 mt-1">{{ $asset->po_number ?? 'N/A' }}</dd></div>
                <div class="flex flex-col"><dt class="font-medium text-gray-500">Nomor BAST</dt><dd class="text-gray-900 mt-1">{{ $asset->nomor ?? 'N/A' }}</dd></div>
                <div class="flex flex-col"><dt class="font-medium text-gray-500">Kode Aktiva</dt><dd class="text-gray-900 mt-1">{{ $asset->code_aktiva ?? 'N/A' }}</dd></div>
                <div class="flex flex-col"><dt class="font-medium text-gray-500">Sumber Dana</dt><dd class="text-gray-900 mt-1">{{ $asset->sumber_dana ?? 'N/A' }}</dd></div>
            </dl>
        </div>

        {{-- Informasi Tambahan --}}
        <div class="bg-white p-6 rounded-xl border">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 border-b pb-3">Informasi Tambahan</h3>
            <div class="text-sm space-y-5">
                <div><dt class="font-medium text-gray-500">Item Termasuk</dt><dd class="text-gray-700 mt-1 prose-sm max-w-none">{{ $asset->include_items ?? 'N/A' }}</dd></div>
                <div><dt class="font-medium text-gray-500">Peruntukan</dt><dd class="text-gray-700 mt-1 prose-sm max-w-none">{{ $asset->peruntukan ?? 'N/A' }}</dd></div>
                <div><dt class="font-medium text-gray-500">Keterangan</dt><dd class="text-gray-700 mt-1 prose-sm max-w-none">{{ $asset->keterangan ?? 'N/A' }}</dd></div>
            </div>
        </div>
        
        {{-- Histori Pengguna --}}
        <div class="bg-white p-6 rounded-xl border">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 border-b pb-3">Histori Pengguna</h3>
            <ul class="space-y-4 text-sm">
                @forelse ($asset->history as $h)
                    <li class="border-b border-gray-200 pb-3 last:border-b-0">
                        <div class="flex justify-between items-center">
                            <p class="font-semibold text-gray-800">{{ optional($h->user)->nama_pengguna ?? 'Pengguna Dihapus' }}</p>
                            @if(is_null($h->tanggal_selesai) && $asset->user_id == $h->user_id)
                                <span class="flex-shrink-0 text-xs bg-green-100 text-green-800 font-semibold px-2 py-1 rounded-full">Saat Ini</span>
                            @endif
                        </div>
                        <div class="mt-1">
                            <p class="text-xs text-gray-500">{{ optional($h->user)->jabatan ?? 'Jabatan tidak diketahui' }}</p>
                            <p class="text-xs text-gray-400 mt-1">
                                <span>Mulai: {{ \Carbon\Carbon::parse($h->tanggal_mulai)->format('d M Y') }}</span>
                                @if($h->tanggal_selesai)
                                    <span> - Selesai: {{ \Carbon\Carbon::parse($h->tanggal_selesai)->format('d M Y') }}</span>
                                @endif
                            </p>
                        </div>
                    </li>
                @empty
                    <li class="text-gray-500 text-center py-4">Tidak ada riwayat pengguna.</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
