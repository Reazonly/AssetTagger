<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Aset - {{ $asset->code_asset }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; line-height: 1.6; color: #333; }
        .container { width: 100%; margin: 0 auto; }
        .header { text-align: center; border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 25px; }
        .header h1 { margin: 0; font-size: 24px; color: #000; }
        .header p { margin: 5px 0 0; font-size: 16px; color: #555; }
        .section { margin-bottom: 25px; page-break-inside: avoid; }
        .section h3 { font-size: 16px; margin-bottom: 10px; border-bottom: 1px solid #ccc; padding-bottom: 5px; color: #222; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 8px 0; vertical-align: top; }
        td.label { font-weight: bold; width: 180px; color: #555; }
        .footer { text-align: center; font-size: 10px; color: #888; position: fixed; bottom: 0; width: 100%; }
        .history-table th, .history-table td { border-bottom: 1px solid #eee; padding: 8px; text-align: left; }
        .history-table th { background-color: #f7f7f7; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Detail Aset</h1>
            <p>{{ $asset->code_asset }}</p>
        </div>

        <div class="section">
            <h3>Informasi Umum</h3>
            <table>
                <tr><td class="label">Nama Barang</td><td>: {{ $asset->nama_barang }}</td></tr>
                <tr><td class="label">Pengguna Saat Ini</td><td>: {{ $asset->user->nama_pengguna }}</td></tr>
                <tr><td class="label">Jabatan</td><td>: {{ $asset->user->jabatan }}</td></tr>
                <tr><td class="label">Departemen</td><td>: {{ $asset->user->departemen }}</td></tr>
                <tr><td class="label">Merk/Tipe</td><td>: {{ $asset->merk_type ?? 'N/A' }}</td></tr>
                <tr><td class="label">Serial Number</td><td>: {{ $asset->serial_number ?? 'N/A' }}</td></tr>
                <tr><td class="label">Kondisi</td><td>: {{ $asset->kondisi ?? 'N/A' }}</td></tr>
                <tr><td class="label">Lokasi Fisik</td><td>: {{ $asset->lokasi ?? 'N/A' }}</td></tr>
                <tr><td class="label">Jumlah</td><td>: {{ $asset->jumlah }} {{ $asset->satuan }}</td></tr>
            </table>
        </div>

        <div class="section">
            <h3>Spesifikasi & Deskripsi</h3>
            @if($asset->spec_input_type == 'manual' && !empty($asset->spesifikasi_manual))
                <p>{{ $asset->spesifikasi_manual }}</p>
            @elseif($asset->spec_input_type == 'detailed')
                <table>
                    <tr><td class="label">Processor</td><td>: {{ $asset->processor ?? 'N/A' }}</td></tr>
                    <tr><td class="label">Memory (RAM)</td><td>: {{ $asset->memory_ram ?? 'N/A' }}</td></tr>
                    <tr><td class="label">Storage</td><td>: {{ $asset->hdd_ssd ?? 'N/A' }}</td></tr>
                    <tr><td class="label">Graphics</td><td>: {{ $asset->graphics ?? 'N/A' }}</td></tr>
                    <tr><td class="label">Layar (LCD)</td><td>: {{ $asset->lcd ?? 'N/A' }}</td></tr>
                </table>
            @else
                <p>Tidak ada detail spesifikasi yang diberikan.</p>
            @endif
        </div>

        <div class="section">
            <h3>Informasi Pembelian & Dokumen</h3>
            <table>
                <tr><td class="label">Tanggal Beli</td><td>: {{ ($asset->tanggal_pembelian instanceof \Carbon\Carbon) ? $asset->tanggal_pembelian->isoFormat('D MMMM YYYY') : 'N/A' }}</td></tr>
                <tr><td class="label">Harga</td><td>: Rp {{ number_format($asset->harga_total ?? 0, 0, ',', '.') }}</td></tr>
                <tr><td class="label">Nomor PO</td><td>: {{ $asset->po_number ?? 'N/A' }}</td></tr>
                <tr><td class="label">Nomor BAST</td><td>: {{ $asset->nomor ?? 'N/A' }}</td></tr>
                <tr><td class="label">Kode Aktiva</td><td>: {{ $asset->code_aktiva ?? 'N/A' }}</td></tr>
                <tr><td class="label">Sumber Dana</td><td>: {{ $asset->sumber_dana ?? 'N/A' }}</td></tr>
            </table>
        </div>

        {{-- Section Informasi Tambahan --}}
        <div class="section">
            <h3>Informasi Tambahan</h3>
            <table>
                <tr><td class="label" style="vertical-align: top;">Item Termasuk</td><td style="vertical-align: top;">: {{ $asset->include_items ?? 'N/A' }}</td></tr>
                <tr><td class="label" style="vertical-align: top;">Peruntukan</td><td style="vertical-align: top;">: {{ $asset->peruntukan ?? 'N/A' }}</td></tr>
                <tr><td class="label" style="vertical-align: top;">Keterangan</td><td style="vertical-align: top;">: {{ $asset->keterangan ?? 'N/A' }}</td></tr>
            </table>
        </div>

        {{-- Section Histori Pengguna --}}
        <div class="section">
            <h3>Histori Pengguna</h3>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Pengguna</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Selesai</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($asset->history as $h)
                        <tr>
                            <td>
                                {{ $h->user->nama_pengguna }}<br>
                                <small style="color: #666;">{{ $h->user->jabatan }}</small>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($h->tanggal_mulai)->isoFormat('D MMMM YYYY') }}</td>
                            <td>
                                @if($h->tanggal_selesai)
                                    {{ \Carbon\Carbon::parse($h->tanggal_selesai)->isoFormat('D MMMM YYYY') }}
                                @elseif($asset->user_id == $h->user_id)
                                    <span style="font-weight: bold; color: #28a745;">Saat Ini</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center;">Tidak ada riwayat pengguna.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="footer">
            Dokumen ini dibuat secara otomatis pada {{ date('d M Y H:i') }}
        </div>
    </div>
</body>
</html>