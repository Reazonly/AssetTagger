@extends('layouts.app')
@section('title', 'Detail Aset - ' . $asset->code_asset)
@section('content')
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">{{ $asset->nama_barang }}</h1>
            <p class="text-lg text-emerald-600 font-mono">{{ $asset->code_asset }}</p>
        </div>
        <div>
            <a href="{{ route('assets.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 mr-4">Kembali</a>
            <a href="{{ route('assets.edit', $asset->id) }}" class="bg-emerald-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-emerald-700">Edit Aset</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        {{-- KOLOM KIRI (INFORMASI DETAIL) --}}
        <div class="md:col-span-2 space-y-8">
            
            {{-- Informasi Umum --}}
            <div class="bg-white p-6 rounded-lg border shadow-sm">
                <h3 class="text-xl font-semibold mb-4 text-gray-700 border-b pb-2">Informasi Umum</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                    <div><strong class="text-gray-500 block">Pengguna Saat Ini:</strong> {{ $asset->user->nama_pengguna ?? 'Tidak ada' }}</div>
                    <div><strong class="text-gray-500 block">Jabatan:</strong> {{ $asset->user->jabatan ?? 'N/A' }}</div>
                    <div><strong class="text-gray-500 block">Departemen:</strong> {{ $asset->user->departemen ?? 'N/A' }}</div>
                    <div class="col-span-1 sm:col-span-2 md:col-span-3 mt-4 pt-4 border-t"></div>
                    <div><strong class="text-gray-500 block">Merk/Tipe:</strong> {{ $asset->merk_type ?? 'N/A' }}</div>
                    <div><strong class="text-gray-500 block">Serial Number:</strong> {{ $asset->serial_number ?? 'N/A' }}</div>
                    <div><strong class="text-gray-500 block">Kondisi:</strong> {{ $asset->kondisi ?? 'N/A' }}</div>
                    <div><strong class="text-gray-500 block">Lokasi Fisik:</strong> {{ $asset->lokasi ?? 'N/A' }}</div>
                    <div><strong class="text-gray-500 block">Jumlah:</strong> {{ $asset->jumlah }} {{ $asset->satuan }}</div>
                </div>
            </div>

            {{-- Spesifikasi Teknis --}}
            <div class="bg-white p-6 rounded-lg border shadow-sm">
                <h3 class="text-xl font-semibold mb-4 text-gray-700 border-b pb-2">Spesifikasi & Deskripsi</h3>
                @if($asset->spec_input_type == 'manual' && !empty($asset->spesifikasi_manual))
                    <div class="text-sm text-gray-700 whitespace-pre-wrap">{{ $asset->spesifikasi_manual }}</div>
                @elseif($asset->spec_input_type == 'detailed' && ($asset->processor || $asset->memory_ram))
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                        <div><strong class="text-gray-500 block">Processor:</strong> {{ $asset->processor ?? 'N/A' }}</div>
                        <div><strong class="text-gray-500 block">Memory (RAM):</strong> {{ $asset->memory_ram ?? 'N/A' }}</div>
                        <div><strong class="text-gray-500 block">Storage:</strong> {{ $asset->hdd_ssd ?? 'N/A' }}</div>
                        <div><strong class="text-gray-500 block">Graphics:</strong> {{ $asset->graphics ?? 'N/A' }}</div>
                        <div class="col-span-1 sm:col-span-2"><strong class="text-gray-500 block">Layar (LCD):</strong> {{ $asset->lcd ?? 'N/A' }}</div>
                    </div>
                @else
                    <p class="text-sm text-gray-500">Tidak ada detail spesifikasi yang diberikan.</p>
                @endif
            </div>

            {{-- Informasi Pembelian & Dokumen --}}
            <div class="bg-white p-6 rounded-lg border shadow-sm">
                <h3 class="text-xl font-semibold mb-4 text-gray-700 border-b pb-2">Informasi Pembelian & Dokumen</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                    <div><strong class="text-gray-500 block">Tanggal Beli:</strong> {{ $asset->tanggal_pembelian ? $asset->tanggal_pembelian->isoFormat('D MMMM YYYY') : 'N/A' }}</div>
                    <div><strong class="text-gray-500 block">Harga:</strong> Rp {{ number_format($asset->harga_total, 0, ',', '.') }}</div>
                    <div><strong class="text-gray-500 block">Nomor PO:</strong> {{ $asset->po_number ?? 'N/A' }}</div>
                    <div><strong class="text-gray-500 block">Nomor BAST:</strong> {{ $asset->nomor ?? 'N/A' }}</div>
                    <div><strong class="text-gray-500 block">Kode Aktiva:</strong> {{ $asset->code_aktiva ?? 'N/A' }}</div>
                    <div><strong class="text-gray-500 block">Sumber Dana:</strong> {{ $asset->sumber_dana ?? 'N/A' }}</div>
                </div>
            </div>
            
            {{-- Informasi Tambahan --}}
            <div class="bg-white p-6 rounded-lg border shadow-sm">
                <h3 class="text-xl font-semibold mb-4 text-gray-700 border-b pb-2">Informasi Tambahan</h3>
                <div class="text-sm space-y-4">
                    <div><strong class="text-gray-500 block">Item Termasuk:</strong><p class="text-gray-700 mt-1">{{ $asset->include_items ?? 'N/A' }}</p></div>
                    <div><strong class="text-gray-500 block">Peruntukan:</strong><p class="text-gray-700 mt-1">{{ $asset->peruntukan ?? 'N/A' }}</p></div>
                    <div><strong class="text-gray-500 block">Keterangan:</strong><p class="text-gray-700 mt-1">{{ $asset->keterangan ?? 'N/A' }}</p></div>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN (TINDAKAN & HISTORI) --}}
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-lg shadow-md text-center border">
                <h3 class="text-xl font-semibold mb-4">QR Code</h3>
                <div class="flex justify-center">{!! $qrCode !!}</div>
                <a href="{{ route('assets.print', ['ids[]' => $asset->id]) }}" target="_blank" class="mt-4 inline-block w-full bg-gray-200 py-2 rounded-md hover:bg-gray-300 transition">Cetak Label</a>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md border">
                <h3 class="text-xl font-semibold mb-4">Histori Pengguna</h3>
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
    </div>
@endsection
