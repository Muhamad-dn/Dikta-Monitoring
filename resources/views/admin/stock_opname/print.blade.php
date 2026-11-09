<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara Stock Opname - {{ $stockOpname->opname_code }}</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.4;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .company-title {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .company-sub {
            font-size: 10px;
            color: #64748b;
            margin-top: 2px;
        }
        .doc-badge {
            text-align: right;
        }
        .doc-badge h2 {
            margin: 0;
            font-size: 14px;
            color: #2563eb;
        }
        .doc-badge p {
            margin: 2px 0 0 0;
            font-family: monospace;
            font-size: 11px;
            font-weight: bold;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 16px;
        }
        .info-item {
            display: flex;
        }
        .info-label {
            width: 130px;
            color: #64748b;
            font-weight: bold;
        }
        .info-value {
            flex: 1;
            font-weight: 600;
        }
        .summary-cards {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
        }
        .card {
            flex: 1;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px 10px;
            text-align: center;
        }
        .card .title {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
        }
        .card .value {
            font-size: 16px;
            font-weight: bold;
            margin-top: 3px;
        }
        .card-match { background: #ecfdf5; border-color: #a7f3d0; color: #059669; }
        .card-missing { background: #fff1f2; border-color: #fecdd3; color: #e11d48; }
        .card-anomaly { background: #fffbeb; border-color: #fde68a; color: #d97706; }
        .card-system { background: #f8fafc; border-color: #e2e8f0; color: #0f172a; }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            color: #334155;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .status-tag {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }
        .tag-match { background: #d1fae5; color: #065f46; }
        .tag-missing { background: #ffe4e6; color: #9f1239; }
        .tag-anomaly { background: #fef3c7; color: #92400e; }

        .notes-box {
            background: #f8fafc;
            border-left: 3px solid #2563eb;
            padding: 8px 12px;
            margin-bottom: 24px;
            font-size: 10px;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .sig-col {
            width: 30%;
            text-align: center;
        }
        .sig-title {
            font-weight: bold;
            font-size: 10px;
            color: #475569;
            margin-bottom: 60px;
        }
        .sig-name {
            font-weight: bold;
            border-top: 1px solid #334155;
            padding-top: 4px;
            font-size: 11px;
        }
        .sig-role {
            font-size: 9px;
            color: #64748b;
        }

        .no-print {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #2563eb;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            border: none;
            font-weight: bold;
            font-size: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="no-print">🖨️ Cetak / Simpan PDF</button>

    <!-- Header Dokumen Resmi -->
    <div class="header">
        <div>
            <div class="company-title">PT DIKTA • DEVTRACK INVENTORY</div>
            <div class="company-sub">Sistem Manajemen & Monitoring Perangkat Jaringan Telekomunikasi</div>
        </div>
        <div class="doc-badge">
            <h2>BERITA ACARA STOCK OPNAME</h2>
            <p>{{ $stockOpname->opname_code }}</p>
        </div>
    </div>

    <!-- Informasi Pelaksanaan -->
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">Judul Sesi</span>
            <span class="info-value">: {{ $stockOpname->title }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Periode Audit</span>
            <span class="info-value">: {{ $stockOpname->month_name }} {{ $stockOpname->period_year }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Lokasi Pemeriksaan</span>
            <span class="info-value">: {{ $stockOpname->location_label }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Status Sesi</span>
            <span class="info-value">: {{ $stockOpname->status === 'completed' ? 'SELESAI (TERKUNCI)' : 'SEDANG BERJALAN' }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Petugas Pemeriksa</span>
            <span class="info-value">: {{ $stockOpname->conductor ? $stockOpname->conductor->name : 'Administrator' }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Tanggal Pelaksanaan</span>
            <span class="info-value">: {{ $stockOpname->completed_at ? $stockOpname->completed_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }} WIB</span>
        </div>
    </div>

    <!-- Ringkasan Angka Audit -->
    <div class="summary-cards">
        <div class="card card-system">
            <div class="title">Target di Sistem</div>
            <div class="value">{{ $stockOpname->total_system }} <span style="font-size: 10px; font-weight: normal;">Unit</span></div>
        </div>
        <div class="card card-match">
            <div class="title">Fisik Ada (Match)</div>
            <div class="value">{{ $stockOpname->total_matched }} <span style="font-size: 10px; font-weight: normal;">Unit ({{ $stockOpname->total_system > 0 ? round(($stockOpname->total_matched / $stockOpname->total_system) * 100, 1) : 0 }}%)</span></div>
        </div>
        <div class="card card-missing">
            <div class="title">Selisih Kurang (Missing)</div>
            <div class="value">{{ $stockOpname->total_missing }} <span style="font-size: 10px; font-weight: normal;">Unit</span></div>
        </div>
        <div class="card card-anomaly">
            <div class="title">Anomali Posisi</div>
            <div class="value">{{ $stockOpname->total_surplus }} <span style="font-size: 10px; font-weight: normal;">Unit</span></div>
        </div>
    </div>

    @if($stockOpname->notes)
        <div class="notes-box">
            <strong>Catatan / Keterangan Pemeriksa:</strong><br>
            {{ $stockOpname->notes }}
        </div>
    @endif

    <!-- Tabel Rincian Hasil Cek Fisik -->
    <h3 style="font-size: 11px; margin: 0 0 8px 0; text-transform: uppercase;">Daftar Rincian Seluruh Perangkat</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th style="width: 140px;">No. Registrasi</th>
                <th>Nama Perangkat</th>
                <th style="width: 45px;" class="text-center">Area</th>
                <th style="width: 90px;">Status Sistem</th>
                <th style="width: 100px;" class="text-center">Hasil Cek Fisik</th>
                <th style="width: 70px;">Waktu Scan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stockOpname->items as $i => $item)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>
                        <strong>{{ $item->registration_number }}</strong>
                        @if($item->device && $item->device->serial_number)
                            <div style="font-size: 8px; color: #64748b;">SN: {{ $item->device->serial_number }}</div>
                        @endif
                    </td>
                    <td>{{ $item->device_name }}</td>
                    <td class="text-center">{{ $item->area ?? '-' }}</td>
                    <td>{{ str_replace('_', ' ', ucfirst($item->system_status ?? '-')) }}</td>
                    <td class="text-center">
                        @if($item->physical_status === 'matched')
                            <span class="status-tag tag-match">SESUAI (MATCH)</span>
                        @elseif($item->physical_status === 'wrong_location')
                            <span class="status-tag tag-anomaly">ANOMALI POSISI</span>
                        @else
                            <span class="status-tag tag-missing">TIDAK ADA</span>
                        @endif
                    </td>
                    <td>{{ $item->scanned_at ? $item->scanned_at->format('H:i d/m') : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Tanda Tangan Pengesahan -->
    <div class="signatures">
        <div class="sig-col">
            <div class="sig-title">Petugas Audit Lapangan</div>
            <div class="sig-name">{{ $stockOpname->conductor ? $stockOpname->conductor->name : 'Admin Gudang' }}</div>
            <div class="sig-role">Pelaksana Stock Opname</div>
        </div>

        <div class="sig-col">
            <div class="sig-title">Koordinator / Supervisor</div>
            <div class="sig-name">Hendra Setiawan</div>
            <div class="sig-role">Koordinator Lapangan</div>
        </div>

        <div class="sig-col">
            <div class="sig-title">Mengetahui & Menyetujui</div>
            <div class="sig-name">Pimpinan PT DIKTA</div>
            <div class="sig-role">Head of Operations</div>
        </div>
    </div>

</body>
</html>
