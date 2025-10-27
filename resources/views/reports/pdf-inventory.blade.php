// resources/views/reports/pdf-inventory.blade.php

<!DOCTYPE html>
<html>
<head>
    <title>Laporan Detail Aset</title>
    <style>
        /* KRITIS: Memaksa PDF menggunakan orientasi Landscape (A4) */
        @page {
            size: A4 landscape; /* ORIENTASI KE LANDSCAPE */
            margin: 0.5cm; /* Margin yang kecil */
        }

        /* Gaya Dasar */
        body { font-family: sans-serif; font-size: 6.5pt; } /* Ukuran font lebih kecil */
        h1 { font-size: 14pt; margin-bottom: 5px; }
        h2 { font-size: 10pt; margin-top: 0; margin-bottom: 15px; }
        
        /* Gaya Tabel Utama */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 10px; 
            table-layout: auto; /* Dibuat AUTO agar lebih fleksibel dalam pembagian lebar */
        }
        th, td { 
            border: 1px solid #000; 
            padding: 3px; /* Padding lebih kecil */
            text-align: left; 
            vertical-align: top;
            word-wrap: break-word; 
        }
        th { 
            background-color: #28A745; 
            color: #fff;
            text-align: center; 
            font-weight: bold;
        }
        
        /* Gaya Khusus */
        .header-section { 
            margin-bottom: 15px; 
            padding-bottom: 5px;
        }
        .footer-total td { 
            background-color: #FFF3CD; 
            font-weight: bold; 
            padding: 5px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }

    </style>
</head>
<body>
    <div class="header-section">
        <h1>Laporan Detail Aset</h1>
        <h2>Per Tanggal: {{ \Carbon\Carbon::now()->format('d F Y H:i:s') }}</h2>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>No.</th>
                <th>Kode Aset</th>
                <th>Nama Barang</th>
                <th>SN</th>
                <th>Kategori</th>
                <th>Sub-Kategori</th>
                <th>Pengguna Saat Ini</th>
                <th>Perusahaan Pengguna</th> 
                <th>Jabatan</th> 
                <th>Departemen</th> 
                <th>Perusahaan Pemilik</th>
                <th>Lokasi</th> 
                <th>Kondisi</th>
                <th>Harga Total (Rp)</th>
                <th>Tgl Perolehan</th>
                <th>No PO</th>
                <th>Kode Aktiva</th>
                <th>Sumber Dana</th>
                <th>Keterangan</th>
                
                </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($assets as $asset)
                @php
                    // Mapping Data Aset
                    $kategoriName = optional(optional($asset->subCategory)->category)->name ?? 'N/A';
                    $subCategoryName = optional($asset->subCategory)->name ?? 'N/A';
                    $assetUser = optional($asset->assetUser);
                    $assetUserName = $assetUser->nama ?? 'Stok';
                    $userCompanyName = optional(optional($assetUser)->company)->name ?? ($assetUser ? '-' : 'N/A');
                    $userJabatan = $assetUser->jabatan ?? ($assetUser ? '-' : 'N/A');
                    $userDepartemen = $assetUser->departemen ?? ($assetUser ? '-' : 'N/A');
                    $companyName = optional($asset->company)->name ?? 'N/A';
                    $location = $asset->lokasi ?? $asset->location ?? 'N/A'; 
                    $tanggalPembelian = $asset->tanggal_pembelian ? \Carbon\Carbon::parse($asset->tanggal_pembelian)->format('d-m-Y') : '-';
                @endphp
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>{{ $asset->code_asset ?? '-' }}</td>
                    <td>{{ $asset->nama_barang ?? '-' }}</td>
                    <td>{{ $asset->serial_number ?? '-' }}</td>
                    <td>{{ $kategoriName }}</td>
                    <td>{{ $subCategoryName }}</td>
                    <td>{{ $assetUserName }}</td>
                    <td>{{ $userCompanyName }}</td>
                    <td>{{ $userJabatan }}</td>
                    <td>{{ $userDepartemen }}</td>
                    <td>{{ $companyName }}</td>
                    <td>{{ $location }}</td>
                    <td>{{ $asset->kondisi ?? '-' }}</td>
                    <td class="text-right">Rp{{ number_format($asset->harga_total ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $tanggalPembelian }}</td>
                    <td>{{ $asset->po_number ?? '-' }}</td>
                    <td>{{ $asset->code_aktiva ?? '-' }}</td>
                    <td>{{ $asset->sumber_dana ?? '-' }}</td>
                    <td>{{ $asset->keterangan ?? '-' }}</td>
                    
                    </tr>
            @empty
                <tr>
                    <td colspan="19" class="text-center">
                        Tidak ada data aset yang ditemukan.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="footer-total">
                <td colspan="12" class="text-left">TOTAL KESELURUHAN</td>
                
                <td class="text-center">Jumlah Aset: {{ number_format($assetCount, 0, ',', '.') }}</td>
                
                <td class="text-right">Rp{{ number_format($totalHarga, 0, ',', '.') }}</td>
                
                <td colspan="5" style="background-color: #FFF3CD;"></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>