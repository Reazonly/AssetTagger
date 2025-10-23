<!DOCTYPE html>
<html>
<head>
    <title>Laporan Inventarisasi Aset</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; }
        h1 { font-size: 16pt; margin-bottom: 5px; }
        h2 { font-size: 12pt; margin-top: 5px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; text-align: center; }
        .footer-total td { background-color: #ddd; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h1>Laporan Inventarisasi Aset</h1>
    <h2>Per Tanggal: {{ \Carbon\Carbon::now()->format('d F Y H:i:s') }}</h2>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 40%;">Kategori Aset</th>
                <th style="width: 15%;">Kondisi</th>
                <th style="width: 20%;">Jumlah Aset</th>
                <th style="width: 20%;">Total Nilai (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; $grandTotalValue = 0; $grandTotalAssets = 0; @endphp
            @foreach($inventorySummary as $item)
                @php 
                    $grandTotalValue += $item->total_value;
                    $grandTotalAssets += $item->total_assets;
                @endphp
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>{{ $item->category_name }}</td>
                    <td class="text-center">{{ $item->kondisi }}</td>
                    <td class="text-center">{{ number_format($item->total_assets, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->total_value, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="footer-total">
                <td colspan="3" class="text-right">TOTAL KESELURUHAN:</td>
                <td class="text-center">{{ number_format($grandTotalAssets, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($grandTotalValue, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>