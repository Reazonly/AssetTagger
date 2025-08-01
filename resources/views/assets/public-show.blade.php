@extends('layouts.public')
@section('title', 'Detail Aset - ' . $asset->code_asset)
@section('content')
    <div class="border-b pb-4 mb-8">
        <h1 class="text-3xl font-bold text-gray-800">{{ $asset->nama_barang }}</h1>
        <p class="text-lg text-emerald-600 font-mono">{{ $asset->code_asset }}</p>
    </div>

    <div class="space-y-8">
        {{-- Informasi Umum --}}
        <div class="bg-white p-6 rounded-lg border">
            <h3 class="text-xl font-semibold mb-4 text-gray-700 border-b pb-2">Informasi Umum</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                <div><strong class="text-gray-500 block">Kategori:</strong> {{ $asset->category->name ?? 'N/A' }}</div>
                <div><strong class="text-gray-500 block">Perusahaan:</strong> {{ $asset->company->name ?? 'N/A' }}</div>
                
                @if($asset->category && $asset->category->requires_merk)
                    <div><strong class="text-gray-500 block">Merk:</strong> {{ $asset->merk ?? 'N/A' }}</div>
                @else
                    <div><strong class="text-gray-500 block">Tipe:</strong> {{ $asset->tipe ?? 'N/A' }}</div>
                @endif

                <div><strong class="text-gray-500 block">Serial Number:</strong> {{ $asset->serial_number ?? 'N/A' }}</div>
                <div><strong class="text-gray-500 block">Kondisi:</strong> {{ $asset->kondisi ?? 'N/A' }}</div>
                <div><strong class="text-gray-500 block">Lokasi Fisik:</strong> {{ $asset->lokasi ?? 'N/A' }}</div>
                <div><strong class="text-gray-500 block">Jumlah:</strong> {{ $asset->jumlah }} {{ $asset->satuan }}</div>
                <div class="col-span-1 sm:col-span-2 mt-4 pt-4 border-t"></div>
                <div><strong class="text-gray-500 block">Pengguna Saat Ini:</strong> {{ $asset->user->nama_pengguna ?? 'Tidak ada' }}</div>
                <div><strong class="text-gray-500 block">Jabatan:</strong> {{ $asset->user->jabatan ?? 'N/A' }}</div>
            </div>
        </div>

        {{-- Spesifikasi Teknis --}}
        <div class="bg-white p-6 rounded-lg border">
            <h3 class="text-xl font-semibold mb-4 text-gray-700 border-b pb-2">Spesifikasi & Deskripsi</h3>
            @if($asset->category && !$asset->category->requires_merk)
                <div class="text-sm text-gray-700 whitespace-pre-wrap">{{ $asset->spesifikasi_manual ?? 'Tidak ada deskripsi.' }}</div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div><strong class="text-gray-500 block">Processor:</strong> {{ $asset->processor ?? 'N/A' }}</div>
                    <div><strong class="text-gray-500 block">Memory (RAM):</strong> {{ $asset->memory_ram ?? 'N/A' }}</div>
                    <div><strong class="text-gray-500 block">Storage:</strong> {{ $asset->hdd_ssd ?? 'N/A' }}</div>
                    <div><strong class="text-gray-500 block">Graphics:</strong> {{ $asset->graphics ?? 'N/A' }}</div>
                </div>
            @endif
        </div>

        {{-- Informasi Pembelian & Dokumen --}}
        <div class="bg-white p-6 rounded-lg border">
            <h3 class="text-xl font-semibold mb-4 text-gray-700 border-b pb-2">Informasi Pembelian & Dokumen</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                <div><strong class="text-gray-500 block">Tanggal Beli:</strong> {{ $asset->tanggal_pembelian ? $asset->tanggal_pembelian->isoFormat('D MMMM YYYY') : 'N/A' }}</div>
                <div><strong class="text-gray-500 block">Harga:</strong> Rp {{ number_format($asset->harga_total, 0, ',', '.') }}</div>
            </div>
        </div>
        
        {{-- Histori Pengguna --}}
        <div class="bg-white p-6 rounded-lg border">
            <h3 class="text-xl font-semibold mb-4 text-gray-700 border-b pb-2">Histori Pengguna</h3>
            <ul class="space-y-4 text-sm">
                @forelse ($asset->history as $h)
                    <li class="border-b pb-3">
                        <div class="flex justify-between items-center">
                            <p class="font-semibold text-gray-800">{{ $h->user->nama_pengguna ?? 'Pengguna Dihapus' }}</p>
                            @if(is_null($h->tanggal_selesai) && $asset->user_id == $h->user_id)
                                <span class="flex-shrink-0 text-xs bg-green-100 text-green-800 font-semibold px-2 py-1 rounded-full">Saat Ini</span>
                            @endif
                        </div>
                        <div class="mt-1">
                            <p class="text-xs text-gray-500">{{ $h->user->jabatan ?? 'Jabatan tidak diketahui' }}</p>
                            <p class="text-xs text-gray-400 mt-1">
                                <span>Mulai: {{ \Carbon\Carbon::parse($h->tanggal_mulai)->format('d M Y') }}</span>
                                @if($h->tanggal_selesai)
                                    <span> - Selesai: {{ \Carbon\Carbon::parse($h->tanggal_selesai)->format('d M Y') }}</span>
                                @endif
                            </p>
                        </div>
                    </li>
                @empty
                    <li class="text-gray-500">Tidak ada riwayat pengguna.</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection