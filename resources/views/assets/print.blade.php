<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label Aset</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f2f5;
        }
        .label-container {
            width: 320px;
            height: 180px;
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            margin: 20px auto;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            page-break-after: always;
        }
        .label-container:last-child {
            page-break-after: auto;
        }
        .label-header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .label-header h1 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            color: #333;
            text-transform: uppercase;
        }
        .label-content {
            display: flex;
            flex-grow: 1;
            gap: 15px;
            align-items: center;
        }
        .label-info {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .label-info p {
            margin: 2px 0;
            font-size: 12px;
            color: #555;
        }
        .label-info .asset-name {
            font-size: 15px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 8px;
            word-wrap: break-word;
        }
        .label-info .asset-code {
            font-family: 'Consolas', 'Courier New', Courier, monospace;
            font-size: 13px;
            font-weight: bold;
            color: #28a745; 
        }
        .label-qr {
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .label-qr img, .label-qr svg {
            width: 90px !important;
            height: 90px !important;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
        }
        .label-qr span {
            font-size: 10px;
            margin-top: 6px;
            color: #777;
            font-style: italic;
        }
        .label-footer {
            font-size: 9px;
            text-align: center;
            color: #999;
            margin-top: 10px;
            border-top: 1px dashed #ccc;
            padding-top: 8px;
        }
        @media print {
            body {
                background-color: #ffffff;
            }
            .label-container {
                margin: 0;
                box-shadow: none;
                border: 1px solid #ccc;
            }
        }
    </style>
</head>
<body onload="window.print()">
    @foreach($assets as $asset)
        <div class="label-container">
            <div class="label-header">
                <h1>Label Aset</h1>
            </div>
            <div class="label-content">
                <div class="label-info">
                    <p class="asset-name">{{ Str::limit($asset->nama_barang, 40) }}</p>
                    <p class="asset-code">{{ $asset->code_asset }}</p>
                    <p style="margin-top: 10px;">
                        <strong>Pengguna:</strong><br>
                        <span>{{ optional($asset->assetUser)->nama ?? 'N/A' }}</span>
                    </p>
                </div>
                <div class="label-qr">
                    {!! QrCode::size(100)->generate(route('assets.public.show', $asset->id)) !!}
                    <span>Scan Me</span>
                </div>
            </div>
            <div class="label-footer">
                PT Jhonlin Group - {{ date('Y') }}
            </div>
        </div>
    @endforeach
</body>
</html>