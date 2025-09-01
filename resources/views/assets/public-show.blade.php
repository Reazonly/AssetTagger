@extends('layouts.public')
@section('title', 'Detail Aset - ' . $asset->code_asset)
@section('content')
    <div class="border-b-2 border-gray-800 pb-4 mb-8 text-center">
        <h1 class="text-3xl font-bold text-gray-800">{{ $asset->nama_barang }}</h1>
        <p class="text-lg text-emerald-600 font-mono tracking-wider mt-1">{{ $asset->code_asset }}</p>
    </div>

    <div class="space-y-8">
        {{-- Card 1: Informasi Umum --}}
        <div class="bg-white p-6 rounded-xl border">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 border-b-2 border-gray-800 pb-2">Informasi Umum</h3>
            @php
                $generalInfo = [
                    'Kategori'      => optional($asset->category)->name,
                    'Sub Kategori'  => optional($asset->subCategory)->name,
                    'Perusahaan'    => optional($asset->company)->name,
                    'Pengguna Saat Ini' => optional($asset->assetUser)->nama,
                    'Merk'          => $asset->merk,
                    'Tipe'          => $asset->tipe,
                    'Serial Number' => $asset->serial_number,
                    'Kondisi'       => $asset->kondisi,
                    'Lokasi Fisik'  => $asset->lokasi,
                ];
            @endphp
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5 text-sm mt-4">
                @foreach($generalInfo as $label => $value)
                    @if(!empty($value))
                        <div>
                            <dt class="font-medium text-gray-500">{{ $label }}</dt>
                            <dd class="mt-1 text-gray-900 font-semibold">{{ $value }}</dd>
                        </div>
                    @endif
                @endforeach
            </dl>
        </div>

        {{-- ======================================================= --}}
        {{-- ==== PERBAIKAN: BLOK GAMBAR DIPINDAHKAN KE SINI ==== --}}
        {{-- ======================================================= --}}
        @if($asset->image_path)
            <div class="bg-white p-6 rounded-xl border">
                <h3 class="text-xl font-semibold mb-4 text-gray-800 border-b-2 border-gray-800 pb-2">Gambar Aset</h3>
                <div class="mt-4 flex justify-center items-center">
                    {{-- Menggunakan Storage::url() untuk path yang benar --}}
                    <img src="{{ Storage::url($asset->image_path) }}" alt="Gambar Aset: {{ $asset->nama_barang }}" class="max-w-full md:max-w-lg max-h-96 rounded-md border bg-gray-50">
                </div>
            </div>
        @endif
        {{-- ======================================================= --}}
        {{-- ============== AKHIR BLOK PERBAIKAN =================== --}}
        {{-- ======================================================= --}}

        {{-- Card 2: Spesifikasi Detail --}}
        @if(!empty($asset->specifications) && count(array_filter($asset->specifications)) > 0)
            <div class="bg-white p-6 rounded-xl border">
                <h3 class="text-xl font-semibold mb-4 text-gray-800 border-b-2 border-gray-800 pb-2">Spesifikasi Detail</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5 text-sm mt-4">
                    @foreach($asset->specifications as $key => $value)
                        @if(!empty($value))
                            <div>
                                <dt class="font-medium text-gray-500 capitalize">{{ str_replace('_', ' ', $key) }}</dt>
                                <dd class="mt-1 text-gray-900 font-semibold">{{ $value }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
            </div>
        @endif
        
        {{-- Card 3: Riwayat Pengguna --}}
        <div class="bg-white p-6 rounded-xl border">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 border-b-2 border-gray-800 pb-2">Riwayat Pengguna</h3>
            <ul class="mt-4 space-y-4">
                @forelse ($asset->history as $h)
                    <li class="p-4 bg-gray-50 rounded-lg border">
                        <div class="flex justify-between items-center">
                            <p class="font-semibold text-gray-800">{{ $h->historical_user_name ?? 'Pengguna Dihapus' }}</p>
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
