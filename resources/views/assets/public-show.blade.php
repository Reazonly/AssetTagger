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

                <div class="flex flex-col">
                    <dt class="font-medium text-gray-500">
                        @if(in_array(optional($asset->category)->code, ['ELEC', 'VEHI']))
                            Nama Tipe
                        @else
                            Nama Barang
                        @endif
                    </dt>
                    <dd class="text-gray-900 mt-1">{{ $asset->nama_barang ?? 'N/A' }}</dd>
                </div>
                
                <div class="flex flex-col"><dt class="font-medium text-gray-500">Kategori</dt><dd class="text-gray-900 mt-1">{{ optional($asset->category)->name ?? 'N/A' }}</dd></div>
                <div class="flex flex-col"><dt class="font-medium text-gray-500">Sub Kategori</dt><dd class="text-gray-900 mt-1">{{ optional($asset->subCategory)->name ?? 'N/A' }}</dd></div>
                <div class="flex flex-col"><dt class="font-medium text-gray-500">Perusahaan</dt><dd class="text-gray-900 mt-1">{{ optional($asset->company)->name ?? 'N/A' }}</dd></div>
                
                @if(optional($asset->category)->requires_merk)
                    <div class="flex flex-col"><dt class="font-medium text-gray-500">Merk</dt><dd class="text-gray-900 mt-1">{{ $asset->merk ?? 'N/A' }}</dd></div>
                @else
                    @if($asset->tipe)
                    <div class="flex flex-col"><dt class="font-medium text-gray-500">Tipe</dt><dd class="text-gray-900 mt-1">{{ $asset->tipe }}</dd></div>
                    @endif
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
                
                {{-- Informasi Pengguna --}}
        <div>
            <h3 class="text-lg font-semibold text-gray-800 mb-3">Informasi Pengguna</h3>
            <div class="grid grid-cols-2 gap-4 text-sm border p-4 rounded-md">
                <div class="font-medium text-gray-500">Pengguna Saat Ini</div>
                {{-- PERBAIKAN DI SINI --}}
                <div>{{ optional($asset->assetUser)->nama ?? 'Tidak Ditetapkan' }}</div>
                
                <div class="font-medium text-gray-500">Jabatan</div>
                 {{-- PERBAIKAN DI SINI --}}
                <div>{{ optional($asset->assetUser)->jabatan ?? 'N/A' }}</div>

                <div class="font-medium text-gray-500">Departemen</div>
                 {{-- PERBAIKAN DI SINI --}}
                <div>{{ optional($asset->assetUser)->departemen ?? 'N/A' }}</div>

                <div class="font-medium text-gray-500">Perusahaan</div>
                <div>{{ optional($asset->assetUser->company)->name ?? 'N/A' }}</div>
            </div>
        </div>

        {{-- Spesifikasi --}}
        @if($asset->specifications)
            <div class="bg-white p-6 rounded-xl border">
                <h3 class="text-xl font-semibold mb-4 text-gray-800 border-b pb-3">Spesifikasi & Deskripsi</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5 text-sm">
                    @foreach($asset->specifications as $key => $value)
                        <div class="flex flex-col">
                            <dt class="font-medium text-gray-500">{{ Str::title(str_replace('_', ' ', $key)) }}</dt>
                            <dd class="text-gray-900 mt-1">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        @endif
        
        {{-- ====================================================== --}}
        {{-- PERUBAHAN: MENAMBAHKAN BAGIAN PEMBELIAN & DOKUMEN --}}
        {{-- ====================================================== --}}
        <div class="bg-white p-6 rounded-xl border">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 border-b pb-3">Informasi Pembelian & Dokumen</h3>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5 text-sm">
                @if($asset->tanggal_pembelian)
                <div class="flex flex-col"><dt class="font-medium text-gray-500">Tanggal Beli</dt><dd class="text-gray-900 mt-1">{{ $asset->tanggal_pembelian->isoFormat('D MMMM YYYY') }}</dd></div>
                @endif
                @if($asset->harga_total > 0)
                <div class="flex flex-col"><dt class="font-medium text-gray-500">Harga</dt><dd class="text-gray-900 mt-1">Rp {{ number_format($asset->harga_total, 0, ',', '.') }}</dd></div>
                @endif
                @if($asset->po_number)
                <div class="flex flex-col"><dt class="font-medium text-gray-500">Nomor PO</dt><dd class="text-gray-900 mt-1">{{ $asset->po_number }}</dd></div>
                @endif
                @if($asset->nomor)
                <div class="flex flex-col"><dt class="font-medium text-gray-500">Nomor BAST</dt><dd class="text-gray-900 mt-1">{{ $asset->nomor }}</dd></div>
                @endif
                @if($asset->code_aktiva)
                <div class="flex flex-col"><dt class="font-medium text-gray-500">Kode Aktiva</dt><dd class="text-gray-900 mt-1">{{ $asset->code_aktiva }}</dd></div>
                @endif
                @if($asset->sumber_dana)
                <div class="flex flex-col"><dt class="font-medium text-gray-500">Sumber Dana</dt><dd class="text-gray-900 mt-1">{{ $asset->sumber_dana }}</dd></div>
                @endif
            </dl>
        </div>

        
        <div class="bg-white p-6 rounded-xl border">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 border-b pb-3">Informasi Tambahan</h3>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5 text-sm">
                @if($asset->include_items)
                    <div class="flex flex-col"><dt class="font-medium text-gray-500">Item Termasuk</dt><dd class="text-gray-700 mt-1">{{ $asset->include_items }}</dd></div>
                @endif
                @if($asset->peruntukan)
                    <div class="flex flex-col"><dt class="font-medium text-gray-500">Peruntukan</dt><dd class="text-gray-700 mt-1">{{ $asset->peruntukan }}</dd></div>
                @endif
                @if($asset->keterangan)
                    <div class="flex flex-col sm:col-span-2"><dt class="font-medium text-gray-500">Keterangan</dt><dd class="text-gray-700 mt-1">{{ $asset->keterangan }}</dd></div>
                @endif
            </dl>
        </div>
        

        <div class="bg-white p-6 rounded-xl border">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 border-b pb-3">Histori Pengguna</h3>
            <ul class="space-y-4 text-sm">
                @forelse ($asset->history as $h)
                    <li class="border-b border-gray-200 pb-3 last:border-b-0">
                        <div class="flex justify-between items-center">
                            <p class="font-semibold text-gray-800">{{ optional($h->assetUser)->nama ?? 'Pengguna Dihapus' }}</p>
                            @if(is_null($h->tanggal_selesai) && $asset->user_id == $h->user_id)
                                <span class="flex-shrink-0 text-xs bg-green-100 text-green-800 font-semibold px-2 py-1 rounded-full">Saat Ini</span>
                            @endif
                        </div>
                        <div class="mt-1">
                            <p class="text-xs text-gray-500">{{ optional($h->assetUser)->jabatan ?? 'Jabatan tidak diketahui' }}</p>
                            <p class="text-xs text-gray-500">{{ optional($h->assetUser)->departemen ?? 'Departemen tidak diketahui' }}</p>
                            
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