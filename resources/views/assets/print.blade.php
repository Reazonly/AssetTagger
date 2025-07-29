<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label Aset</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .label-container {
            width: 320px;
            height: 180px;
            background-color: #ffffff;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 12px;
            margin: 20px auto;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .label-header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .label-header h1 {
            margin: 0;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .label-content {
            display: flex;
            flex-grow: 1;
            gap: 12px;
        }
        .label-info {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .label-info p {
            margin: 1px 0;
            font-size: 11px;
        }
        .label-info .asset-name {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 6px;
            word-wrap: break-word;
        }
        .label-info .asset-code {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            font-weight: bold;
            color: #d9534f;
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
        }
        .label-qr span {
            font-size: 9px;
            margin-top: 4px;
            color: #555;
        }
        .label-footer {
            font-size: 8px;
            text-align: center;
            color: #777;
            margin-top: 5px;
            border-top: 1px dashed #ccc;
            padding-top: 5px;
        }
        @media print {
            body {
                background-color: #ffffff;
            }
            .label-container {
                margin: 0;
                box-shadow: none;
                page-break-after: always;
            }
            .label-container:last-child {
                page-break-after: auto;
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
                    <p style="margin-top: 8px;">
                        <strong>Pengguna:</strong><br>
                        <span>{{ $asset->user->nama_pengguna ?? 'N/A' }}</span>
                    </p>
                </div>
                <div class="label-qr">
                    {{-- Mengarahkan QR Code ke halaman publik read-only --}}
                    {!! QrCode::size(100)->generate(route('assets.public.show', $asset->id)) !!}
                    <span>Scan Me</span>
                </div>
            </div>
            <div class="label-footer">
                Nama Perusahaan Anda - {{ date('Y') }}
            </div>
        </div>
    @endforeach
</body>
</html>
