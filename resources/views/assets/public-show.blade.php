@extends('layouts.public')
@section('title', 'Detail Aset - ' . $asset->code_asset)
@section('content')
    <div class="border-b-2 border-gray-800 pb-4 mb-8">
        <h1 class="text-3xl font-bold text-gray-800">{{ $asset->nama_barang }}</h1>
        <p class="text-lg text-emerald-600 font-mono tracking-wider">{{ $asset->code_asset }}</p>
    </div>

    <div class="space-y-8">
        <div class="bg-white p-6 rounded-xl border">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 border-b-2 border-gray-800 pb-2">Informasi Umum</h3>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5 text-sm mt-4">
                <div class="flex flex-col"><dt class="font-medium text-gray-500">Kategori</dt><dd class="text-gray-900 mt-1">{{ optional($asset->category)->name ?? 'N/A' }}</dd></div>
                <div class="flex flex-col"><dt class="font-medium text-gray-500">Sub Kategori</dt><dd class="text-gray-900 mt-1">{{ optional($asset->subCategory)->name ?? 'N/A' }}</dd></div>
                <div class="flex flex-col"><dt class="font-medium text-gray-500">Perusahaan</dt><dd class="text-gray-900 mt-1">{{ optional($asset->company)->name ?? 'N/A' }}</dd></div>
                @if($asset->merk)<div class="flex flex-col"><dt class="font-medium text-gray-500">Merk</dt><dd class="text-gray-900 mt-1">{{ $asset->merk }}</dd></div>@endif
                @if($asset->tipe)<div class="flex flex-col"><dt class="font-medium text-gray-500">Tipe</dt><dd class="text-gray-900 mt-1">{{ $asset->tipe }}</dd></div>@endif
                @if($asset->serial_number)<div class="flex flex-col"><dt class="font-medium text-gray-500">Serial Number</dt><dd class="text-gray-900 mt-1">{{ $asset->serial_number }}</dd></div>@endif
                @if($asset->kondisi)<div class="flex flex-col"><dt class="font-medium text-gray-500">Kondisi</dt><dd class="text-gray-900 mt-1">{{ $asset->kondisi }}</dd></div>@endif
                @if($asset->lokasi)<div class="flex flex-col"><dt class="font-medium text-gray-500">Lokasi Fisik</dt><dd class="text-gray-900 mt-1">{{ $asset->lokasi }}</dd></div>@endif
            </dl>
        </div>

        <div class="bg-white p-6 rounded-xl border">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 border-b-2 border-gray-800 pb-2">Informasi Pengguna</h3>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5 text-sm mt-4">
                <div class="flex flex-col"><dt class="font-medium text-gray-500">Pengguna Saat Ini</dt><dd class="text-gray-900 mt-1">{{ optional($asset->assetUser)->nama ?? 'Tidak Ditetapkan' }}</dd></div>
                <div class="flex flex-col"><dt class="font-medium text-gray-500">Jabatan</dt><dd class="text-gray-900 mt-1">{{ optional($asset->assetUser)->jabatan ?? 'N/A' }}</dd></div>
                <div class="flex flex-col"><dt class="font-medium text-gray-500">Departemen</dt><dd class="text-gray-900 mt-1">{{ optional($asset->assetUser)->departemen ?? 'N/A' }}</dd></div>
                <div class="flex flex-col"><dt class="font-medium text-gray-500">Perusahaan</dt><dd class="text-gray-900 mt-1">{{ optional(optional($asset->assetUser)->company)->name ?? 'N/A' }}</dd></div>
            </dl>
        </div>

        @if($asset->specifications && count($asset->specifications) > 0)
            <div class="bg-white p-6 rounded-xl border">
                <h3 class="text-xl font-semibold mb-4 text-gray-800 border-b-2 border-gray-800 pb-2">Spesifikasi & Deskripsi</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5 text-sm mt-4">
                    @foreach($asset->specifications as $key => $value)
                        <div class="flex flex-col">
                            <dt class="font-medium text-gray-500">{{ Str::title(str_replace('_', ' ', $key)) }}</dt>
                            <dd class="text-gray-900 mt-1">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        @endif
        
        <div class="bg-white p-6 rounded-xl border">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 border-b-2 border-gray-800 pb-2">Histori Pengguna</h3>
            <ul class="space-y-4 text-sm max-h-96 overflow-y-auto pt-4">
                @forelse ($asset->history as $h)
                    <li class="border-b border-gray-300 pb-3 last:border-b-0">
                        <div class="flex justify-between items-center">
                            <p class="font-semibold text-gray-800">{{ optional($h->assetUser)->nama ?? 'Pengguna Dihapus' }}</p>
                            @if(is_null($h->tanggal_selesai))
                                <span class="flex-shrink-0 text-xs bg-green-100 text-green-800 font-semibold px-2 py-1 rounded-full">Saat Ini</span>
                            @endif
                        </div>
                        <div class="mt-1">
                            <p class="text-xs text-gray-500">{{ optional($h->assetUser)->jabatan ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-500">{{ optional($h->assetUser)->departemen ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-500 font-medium">{{ optional(optional($h->assetUser)->company)->name ?? 'Perusahaan tidak diketahui' }}</p>
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
