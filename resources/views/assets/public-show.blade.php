@extends('layouts.public')
@section('title', 'Detail Aset - ' . $asset->code_asset)
@section('content')
    <div class="border-b pb-4 mb-8">
        <h1 class="text-3xl font-bold text-gray-800">{{ $asset->nama_barang }}</h1>
        <p class="text-lg text-emerald-600 font-mono">{{ $asset->code_asset }}</p>
    </div>

    <div class="space-y-8">
        <div class="bg-white p-6 rounded-lg border">
            <h3 class="text-xl font-semibold mb-4 text-gray-700 border-b pb-2">Informasi Umum</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                <div><strong class="text-gray-500 block">Pengguna Saat Ini:</strong> {{ $asset->user->nama_pengguna ?? 'Tidak ada' }}</div>
                <div><strong class="text-gray-500 block">Jabatan:</strong> {{ $asset->user->jabatan ?? 'N/A' }}</div>
                <div><strong class="text-gray-500 block">Departemen:</strong> {{ $asset->user->departemen ?? 'N/A' }}</div>
                <div class="col-span-1 sm:col-span-2 mt-4 pt-4 border-t"></div>
                <div><strong class="text-gray-500 block">Merk/Tipe:</strong> {{ $asset->merk_type ?? 'N/A' }}</div>
                <div><strong class="text-gray-500 block">Kondisi:</strong> {{ $asset->kondisi ?? 'N/A' }}</div>
                <div><strong class="text-gray-500 block">Lokasi Fisik:</strong> {{ $asset->lokasi ?? 'N/A' }}</div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg border">
            <h3 class="text-xl font-semibold mb-4 text-gray-700 border-b pb-2">Spesifikasi & Deskripsi</h3>
            @if($asset->spec_input_type == 'manual' && !empty($asset->spesifikasi_manual))
                <div class="text-sm text-gray-700 whitespace-pre-wrap">{{ $asset->spesifikasi_manual }}</div>
            @elseif($asset->spec_input_type == 'detailed' && ($asset->processor || $asset->memory_ram))
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div><strong class="text-gray-500 block">Processor:</strong> {{ $asset->processor ?? 'N/A' }}</div>
                    <div><strong class="text-gray-500 block">Memory (RAM):</strong> {{ $asset->memory_ram ?? 'N/A' }}</div>
                    <div><strong class="text-gray-500 block">Storage:</strong> {{ $asset->hdd_ssd ?? 'N/A' }}</div>
                    <div><strong class="text-gray-500 block">Graphics:</strong> {{ $asset->graphics ?? 'N/A' }}</div>
                </div>
            @else
                <p class="text-sm text-gray-500">Tidak ada detail spesifikasi yang diberikan.</p>
            @endif
        </div>

        <div class="bg-white p-6 rounded-lg border">
            <h3 class="text-xl font-semibold mb-4 text-gray-700 border-b pb-2">Histori Pengguna</h3>
            <ul class="space-y-4 text-sm">
                @forelse ($asset->history as $h)
                    <li class="border-b pb-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold text-gray-800">{{ $h->user->nama_pengguna ?? 'Pengguna Dihapus' }}</p>
                                <p class="text-xs text-gray-500">{{ $h->user->jabatan ?? 'Jabatan tidak diketahui' }}</p>
                            </div>
                            @if(is_null($h->tanggal_selesai) && $asset->user_id == $h->user_id)
                                <span class="flex-shrink-0 text-xs bg-green-100 text-green-800 font-semibold px-2 py-1 rounded-full">Saat Ini</span>
                            @endif
                        </div>
                        <div class="text-xs text-gray-400 mt-2">
                            <span>Mulai: {{ \Carbon\Carbon::parse($h->tanggal_mulai)->format('d M Y') }}</span>
                            @if($h->tanggal_selesai)
                                <span> - Selesai: {{ \Carbon\Carbon::parse($h->tanggal_selesai)->format('d M Y') }}</span>
                            @endif
                        </div>
                    </li>
                @empty
                    <li class="text-gray-500">Tidak ada riwayat pengguna.</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
