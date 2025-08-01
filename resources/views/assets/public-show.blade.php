@extends('layouts.public')
@section('title', 'Detail Aset - ' . $asset->code_asset)
@section('content')
    <div class="border-b pb-4 mb-6">
        <h1 class="text-3xl font-bold text-gray-800">{{ $asset->nama_barang }}</h1>
        <p class="text-lg text-emerald-600 font-mono">{{ $asset->code_asset }}</p>
    </div>

    {{-- Tombol Aksi Baru --}}
    <div class="flex items-center gap-3 mb-8">
        <a href="{{ route('assets.export', ['ids[]' => $asset->id]) }}" class="flex items-center bg-green-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Export ke Excel
        </a>
        <button id="shareBtn" class="flex items-center bg-blue-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12s-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z" />
            </svg>
            <span id="shareBtnText">Bagikan</span>
        </button>
    </div>

    <div class="space-y-8">
        {{-- Informasi Umum --}}
        <div class="bg-white p-6 rounded-lg border">
            <h3 class="text-xl font-semibold mb-4 text-gray-700 border-b pb-2">Informasi Umum</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                <div><strong class="text-gray-500 block">Pengguna Saat Ini:</strong> {{ $asset->user->nama_pengguna ?? 'Tidak ada' }}</div>
                <div><strong class="text-gray-500 block">Jabatan:</strong> {{ $asset->user->jabatan ?? 'N/A' }}</div>
                <div><strong class="text-gray-500 block">Departemen:</strong> {{ $asset->user->departemen ?? 'N/A' }}</div>
                <div class="col-span-1 sm:col-span-2 mt-4 pt-4 border-t"></div>
                <div><strong class="text-gray-500 block">Merk/Tipe:</strong> {{ $asset->merk_type ?? 'N/A' }}</div>
                <div><strong class="text-gray-500 block">Serial Number:</strong> {{ $asset->serial_number ?? 'N/A' }}</div>
                <div><strong class="text-gray-500 block">Kondisi:</strong> {{ $asset->kondisi ?? 'N/A' }}</div>
                <div><strong class="text-gray-500 block">Lokasi Fisik:</strong> {{ $asset->lokasi ?? 'N/A' }}</div>
                <div><strong class="text-gray-500 block">Jumlah:</strong> {{ $asset->jumlah }} {{ $asset->satuan }}</div>
            </div>
        </div>

        {{-- Spesifikasi Teknis --}}
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
                    <div class="col-span-1 sm:col-span-2"><strong class="text-gray-500 block">Layar (LCD):</strong> {{ $asset->lcd ?? 'N/A' }}</div>
                </div>
            @else
                <p class="text-sm text-gray-500">Tidak ada detail spesifikasi yang diberikan.</p>
            @endif
        </div>

        {{-- Informasi Pembelian & Dokumen --}}
        <div class="bg-white p-6 rounded-lg border">
            <h3 class="text-xl font-semibold mb-4 text-gray-700 border-b pb-2">Informasi Pembelian & Dokumen</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                <div><strong class="text-gray-500 block">Tanggal Beli:</strong> {{ $asset->tanggal_pembelian ? $asset->tanggal_pembelian->isoFormat('D MMMM YYYY') : 'N/A' }}</div>
                <div><strong class="text-gray-500 block">Harga:</strong> Rp {{ number_format($asset->harga_total, 0, ',', '.') }}</div>
                <div><strong class="text-gray-500 block">Nomor PO:</strong> {{ $asset->po_number ?? 'N/A' }}</div>
                <div><strong class="text-gray-500 block">Nomor BAST:</strong> {{ $asset->nomor ?? 'N/A' }}</div>
                <div><strong class="text-gray-500 block">Kode Aktiva:</strong> {{ $asset->code_aktiva ?? 'N/A' }}</div>
                <div><strong class="text-gray-500 block">Sumber Dana:</strong> {{ $asset->sumber_dana ?? 'N/A' }}</div>
            </div>
        </div>
        
        {{-- Informasi Tambahan --}}
        <div class="bg-white p-6 rounded-lg border">
            <h3 class="text-xl font-semibold mb-4 text-gray-700 border-b pb-2">Informasi Tambahan</h3>
            <div class="text-sm space-y-4">
                <div><strong class="text-gray-500 block">Item Termasuk:</strong><p class="text-gray-700 mt-1">{{ $asset->include_items ?? 'N/A' }}</p></div>
                <div><strong class="text-gray-500 block">Peruntukan:</strong><p class="text-gray-700 mt-1">{{ $asset->peruntukan ?? 'N/A' }}</p></div>
                <div><strong class="text-gray-500 block">Keterangan:</strong><p class="text-gray-700 mt-1">{{ $asset->keterangan ?? 'N/A' }}</p></div>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const shareBtn = document.getElementById('shareBtn');
    const shareBtnText = document.getElementById('shareBtnText');

    shareBtn.addEventListener('click', async () => {
        const shareData = {
            title: 'Detail Aset: {{ $asset->nama_barang }}',
            text: 'Lihat detail untuk aset {{ $asset->nama_barang }} ({{ $asset->code_asset }})',
            url: window.location.href
        };

        // Coba gunakan Web Share API (untuk mobile)
        if (navigator.share) {
            try {
                await navigator.share(shareData);
                // Tidak perlu feedback tambahan karena dialog share sudah muncul
            } catch (err) {
                console.error("Gagal membagikan:", err);
            }
        } else {
            // Fallback: Salin ke clipboard (untuk desktop)
            try {
                const textArea = document.createElement('textarea');
                textArea.value = window.location.href;
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);

                // Beri feedback visual
                const originalText = shareBtnText.textContent;
                shareBtnText.textContent = 'Link Disalin!';
                setTimeout(() => {
                    shareBtnText.textContent = originalText;
                }, 2000);

            } catch (err) {
                console.error('Gagal menyalin link:', err);
                alert('Gagal menyalin link ke clipboard.');
            }
        }
    });
});
</script>
@endsection
