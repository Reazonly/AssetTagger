<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Aset - {{ $asset->code_asset }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; line-height: 1.6; color: #333; }
        .container { width: 100%; margin: 0 auto; }
        .header { text-align: center; border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 25px; }
        h1 { margin: 0; font-size: 24px; color: #000; }
        .section { margin-bottom: 25px; page-break-inside: avoid; }
        h3 { font-size: 16px; margin-bottom: 10px; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 8px 0; vertical-align: top; }
        td.label { font-weight: bold; width: 180px; color: #555; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>Detail Aset</h1><p>{{ $asset->code_asset }}</p></div>
        <div class="section">
            <h3>Informasi Umum</h3>
            <table>
                <tr><td class="label">Nama Barang</td><td>: {{ $asset->nama_barang ?? 'N/A' }}</td></tr>
                <tr><td class="label">Kategori</td><td>: {{ optional($asset->category)->name ?? 'N/A' }}</td></tr>
                <tr><td class="label">Sub Kategori</td><td>: {{ optional($asset->subCategory)->name ?? 'N/A' }}</td></tr>
                <tr><td class="label">Perusahaan</td><td>: {{ optional($asset->company)->name ?? 'N/A' }}</td></tr>
                <tr><td class="label">Pengguna</td><td>: {{ optional($asset->assetUser)->nama ?? 'N/A' }}</td></tr>
                @if(optional($asset->category)->requires_merk)
                <tr><td class="label">Merk</td><td>: {{ $asset->merk ?? 'N/A' }}</td></tr>
                @else
                <tr><td class="label">Tipe</td><td>: {{ $asset->tipe ?? 'N/A' }}</td></tr>
                @endif
            </table>
        </div>

        <div class="section">
            <h3>Spesifikasi & Deskripsi</h3>
            @if(!empty($asset->specifications))
                <table>
                    @foreach($asset->specifications as $key => $value)
                    <tr>
                        <td class="label">{{ Str::title(str_replace('_', ' ', $key)) }}</td>
                        <td>: {{ $value ?? 'N/A' }}</td>
                    </tr>
                    @endforeach
                </table>
            @else
                <p>Tidak ada detail spesifikasi yang diberikan.</p>
            @endif
        </div>

        <div class="section">
            <h3>Informasi Pembelian</h3>
            <table>
                <tr><td class="label">Tanggal Beli</td><td>: {{ $asset->tanggal_pembelian ? $asset->tanggal_pembelian->isoFormat('D MMMM YYYY') : 'N/A' }}</td></tr>
                <tr><td class="label">Harga</td><td>: Rp {{ number_format($asset->harga_total ?? 0, 0, ',', '.') }}</td></tr>
            </table>
        </div>
    </div>
</body>
</html>