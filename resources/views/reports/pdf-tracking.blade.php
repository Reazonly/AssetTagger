<!DOCTYPE html>
<html>
<head>
    <title>Laporan Alokasi Aset Saat Ini</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; }
        h1 { font-size: 16pt; margin-bottom: 5px; }
        h2 { font-size: 12pt; margin-top: 5px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; }
    </style>
</head>
<body>
    <h1>Laporan Alokasi Aset Saat Ini</h1>
    <h2>Per Tanggal: {{ \Carbon\Carbon::now()->format('d F Y H:i:s') }}</h2>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 15%;">Kode Aset</th>
                <th style="width: 15%;">Nama Aset</th>
                <th style="width: 10%;">Kategori</th>
                <th style="width: 15%;">Pengguna Saat Ini</th>
                <th style="width: 15%;">Jabatan & Perusahaan</th>
                <th style="width: 15%;">Lokasi Fisik</th> {{-- Kolom Baru --}}
                <th style="width: 10%;">Kondisi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($currentAllocations as $index => $asset)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $asset->code_asset }}</td>
                    <td>{{ $asset->nama_barang }}</td>
                    <td>{{ optional($asset->category)->name ?? 'N/A' }}</td>
                    <td>{{ optional($asset->assetUser)->nama ?? 'N/A' }}</td>
                    <td>
                        {{ optional($asset->assetUser)->jabatan ?? 'N/A' }}<br>
                        ({{ optional(optional($asset->assetUser)->company)->name ?? 'N/A' }})
                    </td>
                    <td>{{ $asset->lokasi ?? 'N/A' }}</td> {{-- Data Baru --}}
                    <td style="text-align: center;">{{ $asset->kondisi }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>