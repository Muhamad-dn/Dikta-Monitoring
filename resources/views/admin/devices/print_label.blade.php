<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Label Perangkat - {{ $device->registration_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .label-container {
            width: 320px;
            background: #ffffff;
            border: 2px solid #000;
            border-radius: 8px;
            padding: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
        }
        .company-name {
            font-size: 14px;
            font-weight: 900;
            letter-spacing: 1px;
            border-bottom: 2px solid #000;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }
        .barcode-box {
            width: 100%;
            max-width: 280px;
            margin: 10px auto 6px;
            display: flex;
            justify-content: center;
        }
        .barcode-box svg {
            width: 100%;
            height: auto;
            max-height: 54px;
        }
        .reg-number {
            font-size: 20px;
            font-weight: 900;
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
            margin: 6px 0;
            background: #000;
            color: #fff;
            padding: 3px 6px;
            border-radius: 4px;
        }
        .details {
            font-size: 11px;
            color: #111;
            margin-top: 6px;
            line-height: 1.4;
            border-top: 1px dashed #666;
            padding-top: 6px;
            text-align: left;
        }
        .details strong {
            font-weight: bold;
        }
        .no-print {
            margin-bottom: 20px;
        }
        .btn-print {
            background: #4f46e5;
            color: #fff;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            font-weight: bold;
        }
        @media print {
            body {
                background: none;
                padding: 0;
            }
            .no-print {
                display: none;
            }
            .label-container {
                box-shadow: none;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak Label Sticker</button>
    </div>

    <div class="label-container">
        <div class="company-name">
            PT DIKTA — ASET PERANGKAT
        </div>

        <div class="barcode-box">
            {!! $device->linear_barcode_svg !!}
        </div>

        <div class="reg-number">
            *{{ $device->registration_number }}*
        </div>

        <div class="details">
            <div><strong>Perangkat:</strong> {{ $device->name }}</div>
            @if($device->customer_name)
                <div><strong>Pelanggan:</strong> {{ $device->customer_name }}</div>
            @endif
            <div><strong>Alamat:</strong> {{ $device->address ?: '-' }}</div>
            @if($device->nojar)
                <div><strong>NOJAR:</strong> {{ $device->nojar }}</div>
            @endif
            @if($device->activity)
                <div><strong>Aktivitas:</strong> {{ $device->activity }}</div>
            @endif
        </div>
    </div>

</body>
</html>
