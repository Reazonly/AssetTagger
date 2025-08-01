@extends('layouts.public')

@section('title', 'Detail Aset - ' . $asset->code_asset)

@section('content')
    <div class="mb-8">
        <h1 class="text-4xl font-extrabold text-gray-900">{{ $asset->nama_barang }}</h1>
        <p class="mt-2 text-xl font-mono text-emerald-600">{{ $asset->code_asset }}</p>
    </div>

    {{-- Action Buttons --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 mb-10">
        <a href="{{ route('assets.export', ['ids[]' => $asset->id]) }}" class="inline-flex items-center px-6 py-3 bg-green-600 text-white font-semibold rounded-lg shadow-md hover:bg-green-700 transition-colors duration-200 text-sm" aria-label="Export asset data to Excel">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Export ke Excel
        </a>
        <a href="{{ route('assets.pdf', $asset->id) }}" class="inline-flex items-center px-6 py-3 bg-red-600 text-white font-semibold rounded-lg shadow-md hover:bg-red-700 transition-colors duration-200 text-sm" aria-label="Download asset details as a PDF">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Download PDF
        </a>
    </div>

    <div class="space-y-10">
        {{-- General Information --}}
        <section class="bg-white p-8 rounded-xl shadow-lg border border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-4">Informasi Umum</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6 text-base">
                <div>
                    <p class="font-semibold text-gray-500 mb-1">Pengguna Saat Ini</p>
                    <p class="text-gray-900">{{ $asset->user->nama_pengguna ?? 'Tidak ada' }}</p>
                </div>
                <div>
                    <p class="font-semibold text-gray-500 mb-1">Jabatan</p>
                    <p class="text-gray-900">{{ $asset->user->jabatan ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="font-semibold text-gray-500 mb-1">Departemen</p>
                    <p class="text-gray-900">{{ $asset->user->departemen ?? 'N/A' }}</p>
                </div>
                <div class="md:col-span-2 border-t pt-6 mt-4"></div>
                <div>
                    <p class="font-semibold text-gray-500 mb-1">Merk/Tipe</p>
                    <p class="text-gray-900">{{ $asset->merk_type ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="font-semibold text-gray-500 mb-1">Serial Number</p>
                    <p class="text-gray-900">{{ $asset->serial_number ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="font-semibold text-gray-500 mb-1">Kondisi</p>
                    <p class="text-gray-900">{{ $asset->kondisi ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="font-semibold text-gray-500 mb-1">Lokasi Fisik</p>
                    <p class="text-gray-900">{{ $asset->lokasi ?? 'N/A' }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="font-semibold text-gray-500 mb-1">Jumlah</p>
                    <p class="text-gray-900">{{ $asset->jumlah }} {{ $asset->satuan }}</p>
                </div>
            </div>
        </section>

        {{-- Specifications & Description --}}
        <section class="bg-white p-8 rounded-xl shadow-lg border border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-4">Spesifikasi & Deskripsi</h2>
            @if($asset->spec_input_type == 'manual' && !empty($asset->spesifikasi_manual))
                <div class="prose max-w-none text-gray-700 whitespace-pre-wrap">
                    {{ $asset->spesifikasi_manual }}
                </div>
            @elseif($asset->spec_input_type == 'detailed' && ($asset->processor || $asset->memory_ram))
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6 text-base">
                    <div>
                        <p class="font-semibold text-gray-500 mb-1">Processor</p>
                        <p class="text-gray-900">{{ $asset->processor ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-500 mb-1">Memory (RAM)</p>
                        <p class="text-gray-900">{{ $asset->memory_ram ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-500 mb-1">Storage</p>
                        <p class="text-gray-900">{{ $asset->hdd_ssd ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-500 mb-1">Graphics</p>
                        <p class="text-gray-900">{{ $asset->graphics ?? 'N/A' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="font-semibold text-gray-500 mb-1">Layar (LCD)</p>
                        <p class="text-gray-900">{{ $asset->lcd ?? 'N/A' }}</p>
                    </div>
                </div>
            @else
                <p class="text-base text-gray-500 italic">Tidak ada detail spesifikasi yang diberikan.</p>
            @endif
        </section>

        {{-- Purchase & Document Information --}}
        <section class="bg-white p-8 rounded-xl shadow-lg border border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-4">Informasi Pembelian & Dokumen</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6 text-base">
                <div>
                    <p class="font-semibold text-gray-500 mb-1">Tanggal Beli</p>
                    <p class="text-gray-900">{{ $asset->tanggal_pembelian ? $asset->tanggal_pembelian->isoFormat('D MMMM YYYY') : 'N/A' }}</p>
                </div>
                <div>
                    <p class="font-semibold text-gray-500 mb-1">Harga</p>
                    <p class="text-gray-900">Rp {{ number_format($asset->harga_total, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="font-semibold text-gray-500 mb-1">Nomor PO</p>
                    <p class="text-gray-900">{{ $asset->po_number ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="font-semibold text-gray-500 mb-1">Nomor BAST</p>
                    <p class="text-gray-900">{{ $asset->nomor ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="font-semibold text-gray-500 mb-1">Kode Aktiva</p>
                    <p class="text-gray-900">{{ $asset->code_aktiva ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="font-semibold text-gray-500 mb-1">Sumber Dana</p>
                    <p class="text-gray-900">{{ $asset->sumber_dana ?? 'N/A' }}</p>
                </div>
            </div>
        </section>
        
        {{-- Additional Information --}}
        <section class="bg-white p-8 rounded-xl shadow-lg border border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-4">Informasi Tambahan</h2>
            <div class="space-y-6 text-base">
                <div>
                    <p class="font-semibold text-gray-500 mb-1">Item Termasuk</p>
                    <p class="text-gray-900">{{ $asset->include_items ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="font-semibold text-gray-500 mb-1">Peruntukan</p>
                    <p class="text-gray-900">{{ $asset->peruntukan ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="font-semibold text-gray-500 mb-1">Keterangan</p>
                    <p class="text-gray-900">{{ $asset->keterangan ?? 'N/A' }}</p>
                </div>
            </div>
        </section>

        {{-- User History --}}
        <section class="bg-white p-8 rounded-xl shadow-lg border border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-4">Histori Pengguna</h2>
            <ul class="space-y-6 text-base">
                @forelse ($asset->history->sortByDesc('tanggal_mulai') as $h)
                    <li class="border-b pb-4 last:border-b-0 last:pb-0">
                        <div class="flex items-center justify-between">
                            <p class="font-semibold text-gray-800">{{ $h->user->nama_pengguna ?? 'Pengguna Dihapus' }}</p>
                            @if(is_null($h->tanggal_selesai) && $asset->user_id == $h->user_id)
                                <span class="bg-green-100 text-green-800 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide">
                                    Saat Ini
                                </span>
                            @endif
                        </div>
                        <div class="mt-2 text-gray-600">
                            <p class="text-sm">{{ $h->user->jabatan ?? 'Jabatan tidak diketahui' }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                <span>Mulai: {{ \Carbon\Carbon::parse($h->tanggal_mulai)->format('d M Y') }}</span>
                                @if($h->tanggal_selesai)
                                    <span> - Selesai: {{ \Carbon\Carbon::parse($h->tanggal_selesai)->format('d M Y') }}</span>
                                @endif
                            </p>
                        </div>
                    </li>
                @empty
                    <li class="text-gray-500 italic">Tidak ada riwayat pengguna.</li>
                @endforelse
            </ul>
        </section>
    </div>
@endsection