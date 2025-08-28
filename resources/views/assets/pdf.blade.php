<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Aset - {{ $asset->code_asset }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; line-height: 1.6; color: #333; }
        .container { width: 100%; margin: 0 auto; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 25px; }
        h1 { margin: 0; font-size: 24px; color: #000; }
        .code { font-size: 16px; font-family: 'Courier New', Courier, monospace; color: #555; margin-top: 5px; }
        .section { margin-bottom: 20px; page-break-inside: avoid; }
        h3 { font-size: 15px; margin-bottom: 10px; border-bottom: 1px solid #ccc; padding-bottom: 5px; color: #000; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 6px 0; vertical-align: top; }
        td.label { font-weight: bold; width: 170px; color: #555; }
        td.separator { width: 15px; text-align: center; }
        .no-data { color: #888; font-style: italic; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Detail Aset</h1>
            <p class="code">{{ $asset->code_asset }}</p>
        </div>

        <div class="section">
            <h3>Informasi Utama</h3>
            <table>
                <tr><td class="label">Nama Barang</td><td class="separator">:</td><td>{{ $asset->nama_barang ?? 'N/A' }}</td></tr>
                <tr><td class="label">Kategori</td><td class="separator">:</td><td>{{ optional($asset->category)->name ?? 'N/A' }}</td></tr>
                <tr><td class="label">Sub Kategori</td><td class="separator">:</td><td>{{ optional($asset->subCategory)->name ?? 'N/A' }}</td></tr>
                <tr><td class="label">Perusahaan Pemilik</td><td class="separator">:</td><td>{{ optional($asset->company)->name ?? 'N/A' }}</td></tr>
                <tr><td class="label">Pengguna Aset</td><td class="separator">:</td><td>{{ optional($asset->assetUser)->nama ?? 'Tidak ada pengguna' }}</td></tr>
                @if($asset->assetUser)
                <tr><td class="label">Jabatan Pengguna</td><td class="separator">:</td><td>{{ optional($asset->assetUser)->jabatan ?? 'N/A' }}</td></tr>
                <tr><td class="label">Departemen Pengguna</td><td class="separator">:</td><td>{{ optional($asset->assetUser)->departemen ?? 'N/A' }}</td></tr>
                <tr><td class="label">Perusahaan Pengguna</td><td class="separator">:</td><td>{{ optional(optional($asset->assetUser)->company)->name ?? 'N/A' }}</td></tr>
                @endif
            </table>
        </div>

        <div class="section">
            <h3>Detail Aset</h3>
            <table>
                <tr><td class="label">Merk</td><td class="separator">:</td><td>{{ $asset->merk ?? 'N/A' }}</td></tr>
                <tr><td class="label">Tipe</td><td class="separator">:</td><td>{{ $asset->tipe ?? 'N/A' }}</td></tr>
                <tr><td class="label">Serial Number</td><td class="separator">:</td><td>{{ $asset->serial_number ?? 'N/A' }}</td></tr>
                <tr><td class="label">Kondisi</td><td class="separator">:</td><td>{{ $asset->kondisi ?? 'N/A' }}</td></tr>
                <tr><td class="label">Lokasi</td><td class="separator">:</td><td>{{ $asset->lokasi ?? 'N/A' }}</td></tr>
                <tr><td class="label">Jumlah</td><td class="separator">:</td><td>{{ $asset->jumlah }} {{ $asset->satuan }}</td></tr>
            </table>
        </div>

        @if(!empty($asset->specifications))
        <div class="section">
            <h3>Spesifikasi Kustom</h3>
            <table>
                @foreach($asset->specifications as $key => $value)
                    <tr>
                        <td class="label">{{ Str::title(str_replace('_', ' ', $key)) }}</td>
                        <td class="separator">:</td>
                        <td>{{ $value ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
        @endif

        <div class="section">
            <h3>Informasi Pembelian & Dokumen</h3>
            <table>
                <tr><td class="label">Tanggal Pembelian</td><td class="separator">:</td><td>{{ $asset->tanggal_pembelian ? $asset->tanggal_pembelian->isoFormat('D MMMM YYYY') : 'N/A' }}</td></tr>
                <tr><td class="label">Harga Total</td><td class="separator">:</td><td>Rp {{ number_format($asset->harga_total ?? 0, 0, ',', '.') }}</td></tr>
                <tr><td class="label">Nomor PO</td><td class="separator">:</td><td>{{ $asset->po_number ?? 'N/A' }}</td></tr>
                <tr><td class="label">Nomor BAST</td><td class="separator">:</td><td>{{ $asset->nomor ?? 'N/A' }}</td></tr>
                <tr><td class="label">Kode Aktiva</td><td class="separator">:</td><td>{{ $asset->code_aktiva ?? 'N/A' }}</td></tr>
                <tr><td class="label">Sumber Dana</td><td class="separator">:</td><td>{{ $asset->sumber_dana ?? 'N/A' }}</td></tr>
            </table>
        </div>

        <div class="section">
            <h3>Informasi Tambahan</h3>
            <table>
                <tr><td class="label">Item Termasuk</td><td class="separator">:</td><td>{{ $asset->include_items ?? 'N/A' }}</td></tr>
                <tr><td class="label">Peruntukan</td><td class="separator">:</td><td>{{ $asset->peruntukan ?? 'N/A' }}</td></tr>
                <tr><td class="label">Keterangan</td><td class="separator">:</td><td>{{ $asset->keterangan ?? 'N/A' }}</td></tr>
            </table>
        </div>

    </div>
</body>
</html>
