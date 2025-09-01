@extends('layouts.app')
@section('title', 'Detail Aset - ' . $asset->code_asset)
@section('content')
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">{{ $asset->nama_barang }}</h1>
            <p class="text-lg text-emerald-600 font-mono tracking-wider">{{ $asset->code_asset }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('assets.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 transition-colors py-2 px-4 rounded-lg bg-gray-200 hover:bg-gray-300 border border-black">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block -mt-1" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                Kembali
            </a>
            
            @can('edit-asset')
            <a href="{{ route('assets.edit', $asset->id) }}" class="bg-blue-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-blue-700 shadow-md transition-colors inline-flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" /><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" /></svg>
                Edit Aset
            </a>
            @endcan

            <a href="{{ route('assets.export', ['ids[]' => $asset->id, 'category_id' => $asset->category_id]) }}" class="bg-green-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-green-700 shadow-md transition-colors inline-flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                Export
            </a>

            <a href="{{ route('assets.pdf', $asset->id) }}" target="_blank" class="bg-red-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-red-700 shadow-md transition-colors inline-flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm2 10a1 1 0 10-2 0v3a1 1 0 102 0v-3zm2-3a1 1 0 011 1v5a1 1 0 11-2 0v-5a1 1 0 011-1zm4-1a1 1 0 10-2 0v7a1 1 0 102 0V8z" clip-rule="evenodd" /></svg>
                Download PDF
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            
            <div class="bg-white p-6 rounded-xl border shadow-sm">
                <h3 class="text-xl font-semibold border-b pb-3 mb-6 text-gray-800 border-b-2 border-black">Informasi Umum</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-5 text-sm">
                    <div class="flex flex-col"><dt class="font-medium text-gray-500">Nama Barang</dt><dd class="text-gray-900 mt-1">{{ $asset->nama_barang ?? 'N/A' }}</dd></div>
                    <div class="flex flex-col"><dt class="font-medium text-gray-500">Kategori</dt><dd class="text-gray-900 mt-1">{{ optional($asset->category)->name ?? 'N/A' }}</dd></div>
                    <div class="flex flex-col"><dt class="font-medium text-gray-500">Sub Kategori</dt><dd class="text-gray-900 mt-1">{{ optional($asset->subCategory)->name ?? 'N/A' }}</dd></div>
                    <div class="flex flex-col"><dt class="font-medium text-gray-500">Perusahaan</dt><dd class="text-gray-900 mt-1">{{ optional($asset->company)->name ?? 'N/A' }}</dd></div>
                    
                    @php
                        $inputType = optional($asset->subCategory)->input_type;
                    @endphp

                    @if($inputType == 'merk' || $inputType == 'merk_dan_tipe' || $asset->merk)
                        <div class="flex flex-col"><dt class="font-medium text-gray-500">Merk</dt><dd class="text-gray-900 mt-1">{{ $asset->merk ?? '-' }}</dd></div>
                    @endif

                    @if($inputType == 'tipe' || $inputType == 'merk_dan_tipe' || $asset->tipe)
                        <div class="flex flex-col"><dt class="font-medium text-gray-500">Tipe</dt><dd class="text-gray-900 mt-1">{{ $asset->tipe ?? '-' }}</dd></div>
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
                    <div class="flex flex-col"><dt class="font-medium text-gray-500">Jumlah</dt><dd class="text-gray-900 mt-1">{{ $asset->jumlah }} {{ $asset->satuan }}</dd></div>
                </dl>
            </div>        

            <div class="bg-white p-6 rounded-xl border shadow-sm">
                <h3 class="text-xl font-semibold border-b pb-3 mb-6 text-gray-800 border-b-2 border-black">Informasi Pengguna</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-5 text-sm">
                    <div class="flex flex-col"><dt class="font-medium text-gray-500">Pengguna Saat Ini</dt><dd class="text-gray-900 mt-1">{{ optional($asset->assetUser)->nama ?? 'Tidak ada' }}</dd></div>
                    <div class="flex flex-col"><dt class="font-medium text-gray-500">Jabatan</dt><dd class="text-gray-900 mt-1">{{ optional($asset->assetUser)->jabatan ?? 'N/A' }}</dd></div>
                    <div class="flex flex-col"><dt class="font-medium text-gray-500">Departemen</dt><dd class="text-gray-900 mt-1">{{ optional($asset->assetUser)->departemen ?? 'N/A' }}</dd></div>
                    <div class="flex flex-col"><dt class="font-medium text-gray-500">Perusahaan</dt><dd class="text-gray-900 mt-1">{{ optional(optional($asset->assetUser)->company)->name ?? 'N/A' }}</dd></div>
                </dl>
            </div>

            @if($asset->specifications)
            <div class="bg-white p-6 rounded-xl border shadow-sm">
                <h3 class="text-xl font-semibold border-b pb-3 mb-6 text-gray-800 border-b-2 border-black">Spesifikasi & Deskripsi</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5 text-sm">
                    @forelse($asset->specifications as $key => $value)
                        <div class="flex flex-col">
                            <dt class="font-medium text-gray-500">{{ Str::title(str_replace('_', ' ', $key)) }}</dt>
                            <dd class="text-gray-900 mt-1">{{ $value ?? 'N/A' }}</dd>
                        </div>
                    @empty
                        <p class="text-gray-500 sm:col-span-2">Tidak ada detail spesifikasi yang diberikan.</p>
                    @endforelse
                </dl>
            </div>
            @endif

            <div class="bg-white p-6 rounded-xl border shadow-sm">
                <h3 class="text-xl font-semibold border-b pb-3 mb-6 text-gray-800 border-b-2 border-black">Informasi Pembelian & Dokumen</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-5 text-sm">
                    @if($asset->tanggal_pembelian)
                    <div class="flex flex-col"><dt class="font-medium text-gray-500">Tanggal Beli</dt><dd class="text-gray-900 mt-1">{{ $asset->tanggal_pembelian ? $asset->tanggal_pembelian->locale('id')->isoFormat('dddd, D MMMM YYYY') : 'N/A' }}</dd></div>
                    @endif
                    @if($asset->harga_total > 0)
                    <div class="flex flex-col"><dt class="font-medium text-gray-500">Harga</dt><dd class="text-gray-900 mt-1">Rp {{ number_format($asset->harga_total, 0, ',', '.') }}</dd></div>
                    @endif
                    @if($asset->po_number)
                    <div class="flex flex-col"><dt class="font-medium text-gray-500">Nomor PO</dt><dd class="text-gray-900 mt-1">{{ $asset->po_number ?? 'N/A' }}</dd></div>
                    @endif
                    @if($asset->nomor)
                    <div class="flex flex-col"><dt class="font-medium text-gray-500">Nomor BAST</dt><dd class="text-gray-900 mt-1">{{ $asset->nomor ?? 'N/A' }}</dd></div>
                    @endif
                    @if($asset->code_aktiva)
                    <div class="flex flex-col"><dt class="font-medium text-gray-500">Kode Aktiva</dt><dd class="text-gray-900 mt-1">{{ $asset->code_aktiva ?? 'N/A' }}</dd></div>
                    @endif
                    @if($asset->sumber_dana)
                    <div class="flex flex-col"><dt class="font-medium text-gray-500">Sumber Dana</dt><dd class="text-gray-900 mt-1">{{ $asset->sumber_dana ?? 'N/A' }}</dd></div>
                    @endif
                </dl>
            </div>
            
            <div class="bg-white p-6 rounded-xl border shadow-sm">
                <h3 class="text-xl font-semibold border-b pb-3 mb-6 text-gray-800 border-b-2 border-black">Informasi Tambahan</h3>
                <div class="text-sm space-y-5">
                    @if($asset->include_items)
                    <div><dt class="font-medium text-gray-500">Item Termasuk</dt><dd class="text-gray-700 mt-1 prose-sm max-w-none">{{ $asset->include_items ?? 'N/A' }}</dd></div>
                    @endif
                    @if($asset->peruntukan)
                    <div><dt class="font-medium text-gray-500">Peruntukan</dt><dd class="text-gray-700 mt-1 prose-sm max-w-none">{{ $asset->peruntukan ?? 'N/A' }}</dd></div>
                    @endif
                    @if($asset->keterangan)
                    <div><dt class="font-medium text-gray-500">Keterangan</dt><dd class="text-gray-700 mt-1 prose-sm max-w-none">{{ $asset->keterangan ?? 'N/A' }}</dd></div>
                    @endif
                </div>
            </div>

            @if($asset->image_path)
            <div class="bg-white p-8 rounded-lg shadow-md border">
                <h3 class="text-xl font-semibold border-b-2 border-black pb-3 mb-6 text-gray-700">Gambar Aset</h3>
                <div class="flex justify-center items-center">
                    <img src="{{ Storage::url($asset->image_path) }}" alt="Gambar Aset: {{ $asset->nama_barang }}" class="max-w-lg max-h-96 rounded-md border">
                </div>
            </div>
        @endif

        </div>

        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white p-6 rounded-xl shadow-sm text-center border">
                <h3 class="text-xl font-semibold mb-4">QR Code</h3>
                <div class="flex justify-center p-2 bg-gray-50 rounded-lg">{!! QrCode::size(200)->generate(route('assets.public.show', $asset->id)) !!}</div>
                <a href="{{ route('assets.print', ['ids[]' => $asset->id]) }}" target="_blank" class="mt-4 inline-flex items-center justify-center gap-2 w-full bg-gray-200 py-2.5 rounded-lg hover:bg-gray-300 transition font-semibold text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v3a2 2 0 002 2h6a2 2 0 002-2v-3h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v3h6v-3z" clip-rule="evenodd" /></svg>
                    Cetak Label
                </a>
            </div>
            <div class="bg-white p-6 rounded-xl border">
                <h3 class="text-xl font-semibold mb-4 text-gray-800 border-b pb-3 border-b-2 border-black">Histori Pengguna</h3>
                <ul class="space-y-4 text-sm max-h-96 overflow-y-auto">
                    @forelse ($asset->history as $h)
                        <li class="border-b border-gray-200 pb-3 last:border-b-0">
                            <div class="flex justify-between items-center">
                                <p class="font-semibold text-gray-800">{{ $h->historical_user_name ?? (optional($h->assetUser)->nama ?? 'Pengguna Dihapus') }}</p>
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
    </div>
@endsection
