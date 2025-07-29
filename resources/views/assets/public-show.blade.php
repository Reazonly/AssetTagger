@extends('layouts.public')
@section('title', 'Detail Aset - ' . $asset->code_asset)
@section('content')
    {{-- Header dengan Aksen Hijau --}}
    <div class="border-b-2 border-emerald-600 pb-4 mb-8 text-center">
        <h1 class="text-4xl font-bold text-gray-800">{{ $asset->nama_barang }}</h1>
        <p class="text-lg text-gray-500 font-mono mt-1">{{ $asset->code_asset }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Kolom Kiri & Tengah --}}
        <div class="lg:col-span-2 space-y-8">
            
            {{-- KOTAK 1: INFORMASI UTAMA & PENGGUNA --}}
            <div class="bg-white p-6 rounded-lg border">
                <div class="flex items-center mb-4">
                    <div class="bg-emerald-100 text-emerald-600 p-2 rounded-lg mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-700">Informasi Umum & Pengguna</h3>
                </div>
                {{-- PERBAIKAN: Menghapus class 'pl-12' --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div><strong class="text-gray-500">Pengguna Saat Ini:</strong> {{ $asset->user->nama_pengguna ?? 'Tidak ada' }}</div>
                    <div><strong class="text-gray-500">Jabatan:</strong> {{ $asset->user->jabatan ?? 'N/A' }}</div>
                    <div><strong class="text-gray-500">Departemen:</strong> {{ $asset->user->departemen ?? 'N/A' }}</div>
                    <div><strong class="text-gray-500">Kondisi:</strong> {{ $asset->kondisi ?? 'N/A' }}</div>
                    <div class="col-span-1 sm:col-span-2"><strong class="text-gray-500">Lokasi Fisik:</strong> {{ $asset->lokasi ?? 'N/A' }}</div>
                </div>
            </div>

            {{-- KOTAK 2: SPESIFIKASI TEKNIS --}}
            <div class="bg-white p-6 rounded-lg border">
                 <div class="flex items-center mb-4">
                    <div class="bg-emerald-100 text-emerald-600 p-2 rounded-lg mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M12 6V3m0 18v-3M5.636 5.636l-1.414-1.414M19.778 19.778l-1.414-1.414M18.364 5.636l-1.414 1.414M4.222 19.778l1.414-1.414M12 12a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-700">Spesifikasi Teknis</h3>
                </div>
                {{-- PERBAIKAN: Menghapus class 'pl-12' --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div><strong class="text-gray-500">Merk/Tipe:</strong> {{ $asset->merk_type ?? 'N/A' }}</div>
                    <div><strong class="text-gray-500">Serial Number:</strong> {{ $asset->serial_number ?? 'N/A' }}</div>
                    <div><strong class="text-gray-500">Processor:</strong> {{ $asset->processor ?? 'N/A' }}</div>
                    <div><strong class="text-gray-500">Memory (RAM):</strong> {{ $asset->memory_ram ?? 'N/A' }}</div>
                    <div><strong class="text-gray-500">Storage:</strong> {{ $asset->hdd_ssd ?? 'N/A' }}</div>
                    <div><strong class="text-gray-500">Graphics (VGA):</strong> {{ $asset->graphics ?? 'N/A' }}</div>
                    <div class="col-span-1 sm:col-span-2"><strong class="text-gray-500">Layar (LCD):</strong> {{ $asset->lcd ?? 'N/A' }}</div>
                </div>
            </div>

            {{-- KOTAK 3: INFORMASI PEMBELIAN --}}
            <div class="bg-white p-6 rounded-lg border">
                 <div class="flex items-center mb-4">
                    <div class="bg-emerald-100 text-emerald-600 p-2 rounded-lg mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-700">Informasi Pembelian</h3>
                </div>
                {{-- PERBAIKAN: Menghapus class 'pl-12' --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <strong class="text-gray-500">Tanggal Beli:</strong> 
                        {{ $asset->tanggal_pembelian ? \Carbon\Carbon::parse($asset->tanggal_pembelian)->isoFormat('dddd, D MMMM') : 'N/A' }}
                    </div>
                    <div>
                        <strong class="text-gray-500">Tahun Beli:</strong> 
                        {{ $asset->thn_pembelian ?? 'N/A' }}
                    </div>
                    <div><strong class="text-gray-500">Nomor PO:</strong> {{ $asset->po_number ?? 'N/A' }}</div>
                    <div><strong class="text-gray-500">Harga:</strong> Rp {{ number_format($asset->harga_total, 0, ',', '.') }}</div>
                    <div><strong class="text-gray-500">Kode Aktiva:</strong> {{ $asset->code_aktiva ?? 'N/A' }}</div>
                    <div><strong class="text-gray-500">Nomor BAST:</strong> {{ $asset->nomor ?? 'N/A' }}</div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan --}}
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-lg shadow-md text-center border">
                <h3 class="text-xl font-semibold mb-4">QR Code</h3>
                <div class="flex justify-center">{!! QrCode::size(200)->generate(route('assets.public.show', $asset)) !!}</div>
                <p class="text-xs text-gray-400 mt-2">Scan untuk melihat detail aset ini</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md border">
                <h3 class="text-xl font-semibold mb-4">Histori Pengguna</h3>
                <ul class="space-y-4 text-sm">
                    @forelse ($asset->history as $h)
                        <li class="border-b pb-3">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $h->user->nama_pengguna ?? 'Pengguna Dihapus' }}</p>
                                    <p class="text-xs text-gray-500">{{ $h->user->jabatan ?? 'N/A' }}</p>
                                </div>
                                @if(is_null($h->tanggal_selesai) && $asset->user_id == $h->user_id)
                                    <span class="text-xs bg-emerald-100 text-emerald-800 font-semibold px-2 py-1 rounded-full">Saat Ini</span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-400 mt-2">
                                <span>{{ \Carbon\Carbon::parse($h->tanggal_mulai)->isoFormat('D MMM YYYY') }}</span>
                                @if($h->tanggal_selesai)
                                    <span> &rarr; {{ \Carbon\Carbon::parse($h->tanggal_selesai)->isoFormat('D MMM YYYY') }}</span>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="text-gray-500">Tidak ada riwayat pengguna.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
