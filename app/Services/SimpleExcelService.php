<?php

namespace App\Services;

use ZipArchive;
use SimpleXMLElement;
use Exception;

class SimpleExcelService
{
    /**
     * Parse an Excel (.xlsx) or CSV (.csv) file into an associative array of rows.
     *
     * @param string $filePath Absolute path to the uploaded file
     * @param string|null $extension 'xlsx' or 'csv' (inferred if null)
     * @return array<int, array<string, mixed>>
     */
    public static function parse(string $filePath, ?string $extension = null): array
    {
        if (!file_exists($filePath)) {
            throw new Exception("File tidak ditemukan: {$filePath}");
        }

        if (!$extension) {
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        }

        $extension = strtolower($extension);

        if ($extension === 'xlsx') {
            return self::parseXlsx($filePath);
        } elseif ($extension === 'csv' || $extension === 'txt') {
            return self::parseCsv($filePath);
        } else {
            throw new Exception("Format file '{$extension}' tidak didukung. Harap unggah file .xlsx atau .csv.");
        }
    }

    /**
     * Parse .xlsx file using built-in ZipArchive and SimpleXML without any external libraries.
     */
    public static function parseXlsx(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new Exception("Gagal membuka file Excel (.xlsx). Pastikan file tidak rusak.");
        }

        // 1. Read shared strings if present
        $sharedStrings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml !== false) {
            $xml = @simplexml_load_string($sharedStringsXml);
            if ($xml && isset($xml->si)) {
                foreach ($xml->si as $si) {
                    $text = '';
                    if (isset($si->t)) {
                        $text = (string)$si->t;
                    } elseif (isset($si->r)) {
                        foreach ($si->r as $run) {
                            $text .= (string)$run->t;
                        }
                    }
                    $sharedStrings[] = $text;
                }
            }
        }

        // 2. Locate sheet1.xml (or first worksheet)
        $sheetXmlContent = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXmlContent === false) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (preg_match('#^xl/worksheets/sheet\d+\.xml$#i', $name)) {
                    $sheetXmlContent = $zip->getFromName($name);
                    break;
                }
            }
        }

        $zip->close();

        if ($sheetXmlContent === false) {
            throw new Exception("Worksheet tidak ditemukan di dalam file Excel.");
        }

        $sheetXml = @simplexml_load_string($sheetXmlContent);
        if (!$sheetXml || !isset($sheetXml->sheetData)) {
            return [];
        }

        $matrix = [];
        foreach ($sheetXml->sheetData->row as $row) {
            $rowNum = (int)$row['r'];
            $rowValues = [];
            $maxColIdx = -1;

            foreach ($row->c as $cell) {
                $cellRef = (string)$cell['r'];
                $colIdx = 0;
                if (preg_match('/^([A-Z]+)(\d+)$/i', $cellRef, $matches)) {
                    $colIdx = self::colLetterToIndex($matches[1]);
                } else {
                    $colIdx = $maxColIdx + 1;
                }

                $maxColIdx = max($maxColIdx, $colIdx);

                $cellType = (string)($cell['t'] ?? '');
                $val = '';
                if ($cellType === 's') {
                    $sId = (int)$cell->v;
                    $val = $sharedStrings[$sId] ?? '';
                } elseif ($cellType === 'inlineStr') {
                    $val = (string)($cell->is->t ?? '');
                } else {
                    $val = (string)($cell->v ?? '');
                }

                $rowValues[$colIdx] = trim($val);
            }

            if ($maxColIdx >= 0) {
                $fullRow = [];
                for ($c = 0; $c <= $maxColIdx; $c++) {
                    $fullRow[$c] = $rowValues[$c] ?? '';
                }
                // Only keep row if not completely empty
                if (array_filter($fullRow, fn($v) => $v !== '')) {
                    $matrix[] = $fullRow;
                }
            }
        }

        return self::matrixToAssociative($matrix);
    }

    /**
     * Parse CSV file with automatic delimiter detection and UTF-8 BOM handling.
     */
    public static function parseCsv(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new Exception("Gagal membaca file CSV.");
        }

        // Detect delimiter
        $sample = fread($handle, 4096);
        rewind($handle);

        $delimiters = [',', ';', "\t", '|'];
        $bestDelimiter = ',';
        $maxCount = -1;
        foreach ($delimiters as $delim) {
            $count = substr_count($sample, $delim);
            if ($count > $maxCount) {
                $maxCount = $count;
                $bestDelimiter = $delim;
            }
        }

        $matrix = [];
        $isFirst = true;

        while (($row = fgetcsv($handle, 0, $bestDelimiter)) !== false) {
            if ($isFirst && !empty($row)) {
                // Strip UTF-8 BOM from first cell if present
                $bom = pack('H*', 'EFBBBF');
                $row[0] = preg_replace("/^{$bom}/", '', $row[0]);
                $isFirst = false;
            }

            $trimmedRow = array_map(fn($v) => trim((string)$v), $row);
            if (array_filter($trimmedRow, fn($v) => $v !== '')) {
                $matrix[] = $trimmedRow;
            }
        }

        fclose($handle);

        return self::matrixToAssociative($matrix);
    }

    /**
     * Convert 2D matrix (header row + data rows) into associative array.
     */
    private static function matrixToAssociative(array $matrix): array
    {
        if (count($matrix) < 2) {
            return [];
        }

        $rawHeaders = array_shift($matrix);
        $normalizedHeaders = [];
        foreach ($rawHeaders as $idx => $header) {
            $normalizedHeaders[$idx] = self::normalizeHeader((string)$header);
        }

        $result = [];
        foreach ($matrix as $rowIndex => $row) {
            $item = [];
            $hasData = false;
            foreach ($normalizedHeaders as $colIdx => $key) {
                if ($key === '') {
                    continue;
                }
                $val = $row[$colIdx] ?? '';
                if ($val !== '') {
                    $hasData = true;
                }
                $item[$key] = $val;
            }

            if ($hasData) {
                $item['_row_number'] = $rowIndex + 2; // 1-indexed, row 1 was header
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * Normalize header name (e.g. "Nomor Registrasi" -> "nomor_registrasi")
     */
    public static function normalizeHeader(string $header): string
    {
        $h = strtolower(trim($header));
        // Remove common punctuation
        $h = preg_replace('/[.\/\\-_\(\)]+/', ' ', $h);
        $h = preg_replace('/\s+/', '_', trim($h));

        // Common aliases mapping
        $aliasMap = [
            'no_registrasi' => 'registration_number',
            'nomor_registrasi' => 'registration_number',
            'no_reg' => 'registration_number',
            'reg' => 'registration_number',
            'registration_number' => 'registration_number',
            'barcode' => 'registration_number',
            'kode_unit' => 'registration_number',
            'id_perangkat' => 'registration_number',

            'serial_number' => 'serial_number',
            'no_seri' => 'serial_number',
            'nomor_seri' => 'serial_number',
            'sn' => 'serial_number',
            'serial' => 'serial_number',

            'nama_perangkat' => 'name',
            'nama' => 'name',
            'name' => 'name',
            'device_name' => 'name',
            'perangkat' => 'name',

            'merk' => 'brand',
            'brand' => 'brand',

            'model' => 'model',
            'tipe_model' => 'model',

            'tipe' => 'type',
            'jenis' => 'type',
            'type' => 'type',

            'area' => 'area',
            'wilayah' => 'area',

            'nojar' => 'nojar',
            'no_jar' => 'nojar',
            'nomor_jaringan' => 'nojar',

            'pelanggan' => 'customer_name',
            'nama_pelanggan' => 'customer_name',
            'customer' => 'customer_name',
            'customer_name' => 'customer_name',

            'alamat' => 'address',
            'lokasi' => 'address',
            'address' => 'address',

            'aktivitas' => 'activity',
            'activity' => 'activity',
            'kegiatan' => 'activity',

            'status' => 'stock_status',
            'status_stok' => 'stock_status',
            'stock_status' => 'stock_status',

            'kondisi' => 'condition',
            'condition' => 'condition',

            'catatan' => 'notes',
            'keterangan' => 'notes',
            'notes' => 'notes',
            'note' => 'notes',
        ];

        return $aliasMap[$h] ?? $h;
    }

    /**
     * Convert Excel column letters (A, B, ..., Z, AA, AB) to 0-based index.
     */
    public static function colLetterToIndex(string $col): int
    {
        $col = strtoupper($col);
        $len = strlen($col);
        $idx = 0;
        for ($i = 0; $i < $len; $i++) {
            $idx = $idx * 26 + (ord($col[$i]) - 64);
        }
        return $idx - 1;
    }

    /**
     * Convert 0-based index to Excel column letter (0 -> A, 27 -> AB).
     */
    public static function indexToColLetter(int $index): string
    {
        $letter = '';
        $index += 1;
        while ($index > 0) {
            $modulo = ($index - 1) % 26;
            $letter = chr(65 + $modulo) . $letter;
            $index = (int)(($index - $modulo) / 26);
        }
        return $letter;
    }

    /**
     * Generate sample CSV template content with UTF-8 BOM for instant Excel compatibility.
     */
    public static function generateSampleCsv(): string
    {
        $headers = [
            'Nomor Registrasi',
            'Serial Number',
            'Nama Perangkat',
            'Merk',
            'Model',
            'Tipe',
            'Area',
            'NOJAR',
            'Nama Pelanggan',
            'Alamat',
            'Aktivitas',
            'Status Stok',
            'Kondisi',
            'Catatan'
        ];

        $sampleRows = [
            [
                'HA0267',
                'B2WN0262020HA0267',
                'FORTIGET 50E',
                'PT DIKTA',
                'FG-50E',
                'ONT',
                'BWA',
                'NJR001',
                'Bank Mandiri Cabang Utama',
                'Jl. Jendral Sudirman No. 45',
                'Spare Dikta',
                'Tersedia di HB JKT',
                'baru',
                'Perangkat cadangan operasional'
            ],
            [
                'HA2068',
                'B2WN0262020HA2068',
                'HUAWEI HG8245H5',
                'Huawei',
                'HG8245H5',
                'ONT',
                'BDA',
                'NJR002',
                'PT Telkom Akses',
                'Jl. Gatot Subroto Kav. 12',
                'PSB',
                'Tersedia di HB CKR',
                'baru',
                'Siap pasang proyek baru'
            ],
            [
                'MK0109',
                'RB951-SN998877',
                'MikroTik RB951Ui-2HnD',
                'MikroTik',
                'RB951Ui',
                'Router',
                'SEOA',
                'NJR003',
                'Kantor BPN Serang',
                'Jl. Ahmad Yani No. 10',
                'Troubleshoot',
                'Tersedia di HB JKT',
                'bekas_bagus',
                'Hasil dismantle tes normal'
            ]
        ];

        $output = fopen('php://temp', 'r+');
        // Add UTF-8 BOM
        fputs($output, "\xEF\xBB\xBF");

        fputcsv($output, $headers);
        foreach ($sampleRows as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Generate sample .xlsx file content as binary string.
     */
    public static function generateSampleXlsx(): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_tpl_');
        $zip = new ZipArchive();
        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception("Gagal membuat template Excel.");
        }

        $headers = [
            'Nomor Registrasi',
            'Serial Number',
            'Nama Perangkat',
            'Merk',
            'Model',
            'Tipe',
            'Area',
            'NOJAR',
            'Nama Pelanggan',
            'Alamat',
            'Aktivitas',
            'Status Stok',
            'Kondisi',
            'Catatan'
        ];

        $sampleRows = [
            [
                'HA0267',
                'B2WN0262020HA0267',
                'FORTIGET 50E',
                'PT DIKTA',
                'FG-50E',
                'ONT',
                'BWA',
                'NJR001',
                'Bank Mandiri Cabang Utama',
                'Jl. Jendral Sudirman No. 45',
                'Spare Dikta',
                'Tersedia di HB JKT',
                'baru',
                'Perangkat cadangan operasional'
            ],
            [
                'HA2068',
                'B2WN0262020HA2068',
                'HUAWEI HG8245H5',
                'Huawei',
                'HG8245H5',
                'ONT',
                'BDA',
                'NJR002',
                'PT Telkom Akses',
                'Jl. Gatot Subroto Kav. 12',
                'PSB',
                'Tersedia di HB CKR',
                'baru',
                'Siap pasang proyek baru'
            ],
            [
                'MK0109',
                'RB951-SN998877',
                'MikroTik RB951Ui-2HnD',
                'MikroTik',
                'RB951Ui',
                'Router',
                'SEOA',
                'NJR003',
                'Kantor BPN Serang',
                'Jl. Ahmad Yani No. 10',
                'Troubleshoot',
                'Tersedia di HB JKT',
                'bekas_bagus',
                'Hasil dismantle tes normal'
            ]
        ];

        // Build sheetData XML
        $sheetDataXml = '<sheetData>';
        // Header row
        $sheetDataXml .= '<row r="1">';
        foreach ($headers as $colIdx => $h) {
            $colLetter = self::indexToColLetter($colIdx);
            $sheetDataXml .= '<c r="' . $colLetter . '1" t="inlineStr"><is><t>' . htmlspecialchars($h, ENT_XML1, 'UTF-8') . '</t></is></c>';
        }
        $sheetDataXml .= '</row>';

        // Data rows
        foreach ($sampleRows as $rIdx => $row) {
            $rowNum = $rIdx + 2;
            $sheetDataXml .= '<row r="' . $rowNum . '">';
            foreach ($row as $colIdx => $val) {
                $colLetter = self::indexToColLetter($colIdx);
                $sheetDataXml .= '<c r="' . $colLetter . $rowNum . '" t="inlineStr"><is><t>' . htmlspecialchars($val, ENT_XML1, 'UTF-8') . '</t></is></c>';
            }
            $sheetDataXml .= '</row>';
        }
        $sheetDataXml .= '</sheetData>';

        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
            '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
            '<Default Extension="xml" ContentType="application/xml"/>' .
            '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' .
            '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' .
            '</Types>';

        $rootRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' .
            '</Relationships>';

        $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
            '<sheets><sheet name="Data Perangkat" sheetId="1" r:id="rId1"/></sheets>' .
            '</workbook>';

        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' .
            '</Relationships>';

        $worksheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
            $sheetDataXml .
            '</worksheet>';

        $zip->addFromString('[Content_Types].xml', $contentTypes);
        $zip->addFromString('_rels/.rels', $rootRels);
        $zip->addFromString('xl/workbook.xml', $workbook);
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);
        $zip->addFromString('xl/worksheets/sheet1.xml', $worksheet);
        $zip->close();

        $content = file_get_contents($tempFile);
        @unlink($tempFile);

        return $content;
    }

    /**
     * Generate styled Stock Opname report as native Excel (.xlsx) file matching the web table.
     */
    public static function exportStockOpnameXlsx(\App\Models\StockOpname $stockOpname): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'opname_xlsx_');
        $zip = new ZipArchive();
        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception("Gagal membuat file Excel Stock Opname.");
        }

        $stockOpname->loadMissing(['conductor', 'items.device', 'items.scanner']);

        $adminName = $stockOpname->admin_name ?: ($stockOpname->conductor ? $stockOpname->conductor->name : 'Administrator');
        $periodText = $stockOpname->month_name . ' ' . $stockOpname->period_year;
        $statusText = $stockOpname->status === 'completed' ? 'Selesai' : 'Sedang Berjalan';
        $completedDate = $stockOpname->completed_at ? $stockOpname->completed_at->format('d/m/Y H:i') : '-';

        $stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
            '<fonts count="9">' .
            '<font><sz val="10"/><color rgb="FF1E293B"/><name val="Calibri"/></font>' .
            '<font><b/><sz val="10"/><color rgb="FF1E293B"/><name val="Calibri"/></font>' .
            '<font><b/><sz val="10.5"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>' .
            '<font><b/><sz val="14"/><color rgb="FF0F172A"/><name val="Calibri"/></font>' .
            '<font><b/><sz val="10"/><color rgb="FF065F46"/><name val="Calibri"/></font>' .
            '<font><b/><sz val="10"/><color rgb="FF991B1B"/><name val="Calibri"/></font>' .
            '<font><b/><sz val="10"/><color rgb="FF92400E"/><name val="Calibri"/></font>' .
            '<font><b/><sz val="10"/><color rgb="FF6B21A8"/><name val="Calibri"/></font>' .
            '<font><i/><sz val="9.5"/><color rgb="FF64748B"/><name val="Calibri"/></font>' .
            '</fonts>' .
            '<fills count="12">' .
            '<fill><patternFill patternType="none"/></fill>' .
            '<fill><patternFill patternType="gray125"/></fill>' .
            '<fill><patternFill patternType="solid"><fgColor rgb="FF1E293B"/></patternFill></fill>' .
            '<fill><patternFill patternType="solid"><fgColor rgb="FFF1F5F9"/></patternFill></fill>' .
            '<fill><patternFill patternType="solid"><fgColor rgb="FFD1FAE5"/></patternFill></fill>' .
            '<fill><patternFill patternType="solid"><fgColor rgb="FFFEE2E2"/></patternFill></fill>' .
            '<fill><patternFill patternType="solid"><fgColor rgb="FFFEF3C7"/></patternFill></fill>' .
            '<fill><patternFill patternType="solid"><fgColor rgb="FFF3E8FF"/></patternFill></fill>' .
            '<fill><patternFill patternType="solid"><fgColor rgb="FFECFDF5"/></patternFill></fill>' .
            '<fill><patternFill patternType="solid"><fgColor rgb="FFFEF2F2"/></patternFill></fill>' .
            '<fill><patternFill patternType="solid"><fgColor rgb="FFFFFBEB"/></patternFill></fill>' .
            '<fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/></patternFill></fill>' .
            '</fills>' .
            '<borders count="2">' .
            '<border><left/><right/><top/><bottom/><diagonal/></border>' .
            '<border>' .
            '<left style="thin"><color rgb="FFCBD5E1"/></left>' .
            '<right style="thin"><color rgb="FFCBD5E1"/></right>' .
            '<top style="thin"><color rgb="FFCBD5E1"/></top>' .
            '<bottom style="thin"><color rgb="FFCBD5E1"/></bottom>' .
            '</border>' .
            '</borders>' .
            '<cellStyleXfs count="1">' .
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>' .
            '</cellStyleXfs>' .
            '<cellXfs count="18">' .
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>' .
            '<xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1"/>' .
            '<xf numFmtId="0" fontId="8" fillId="0" borderId="0" xfId="0" applyFont="1"/>' .
            '<xf numFmtId="0" fontId="1" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment vertical="center"/></xf>' .
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"><alignment vertical="center"/></xf>' .
            '<xf numFmtId="0" fontId="2" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>' .
            '<xf numFmtId="0" fontId="2" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="left" vertical="center"/></xf>' .
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"><alignment horizontal="left" vertical="center"/></xf>' .
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>' .
            '<xf numFmtId="0" fontId="1" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>' .
            '<xf numFmtId="0" fontId="4" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>' .
            '<xf numFmtId="0" fontId="5" fillId="5" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>' .
            '<xf numFmtId="0" fontId="6" fillId="6" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>' .
            '<xf numFmtId="0" fontId="7" fillId="7" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>' .
            '<xf numFmtId="0" fontId="1" fillId="11" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>' .
            '<xf numFmtId="0" fontId="4" fillId="8" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>' .
            '<xf numFmtId="0" fontId="5" fillId="9" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>' .
            '<xf numFmtId="0" fontId="6" fillId="10" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>' .
            '</cellXfs>' .
            '</styleSheet>';

        $rowsXml = '';

        // Row 1: Document Title
        $rowsXml .= '<row r="1" ht="26" customHeight="1">';
        $rowsXml .= '<c r="A1" s="1" t="inlineStr"><is><t>' . htmlspecialchars('PT DIKTA - LAPORAN BERITA ACARA STOCK OPNAME', ENT_XML1, 'UTF-8') . '</t></is></c>';
        $rowsXml .= '</row>';

        // Row 2: Subtitle
        $rowsXml .= '<row r="2" ht="18" customHeight="1">';
        $rowsXml .= '<c r="A2" s="2" t="inlineStr"><is><t>' . htmlspecialchars('Audit Fisik Stok Gudang Berkala vs Catatan Sistem PT DIKTA', ENT_XML1, 'UTF-8') . '</t></is></c>';
        $rowsXml .= '</row>';

        // Row 3: Blank separator
        $rowsXml .= '<row r="3"/>';

        // Row 4: Meta Information
        $rowsXml .= '<row r="4" ht="20" customHeight="1">';
        $rowsXml .= '<c r="A4" s="3" t="inlineStr"><is><t>KODE SESI</t></is></c>';
        $rowsXml .= '<c r="B4" s="4" t="inlineStr"><is><t>' . htmlspecialchars($stockOpname->opname_code, ENT_XML1, 'UTF-8') . '</t></is></c>';
        $rowsXml .= '<c r="D4" s="3" t="inlineStr"><is><t>STATUS SESI</t></is></c>';
        $rowsXml .= '<c r="E4" s="4" t="inlineStr"><is><t>' . htmlspecialchars(strtoupper($statusText), ENT_XML1, 'UTF-8') . '</t></is></c>';
        $rowsXml .= '</row>';

        // Row 5: Periode & Lokasi Gudang
        $rowsXml .= '<row r="5" ht="20" customHeight="1">';
        $rowsXml .= '<c r="A5" s="3" t="inlineStr"><is><t>PERIODE</t></is></c>';
        $rowsXml .= '<c r="B5" s="4" t="inlineStr"><is><t>' . htmlspecialchars($periodText, ENT_XML1, 'UTF-8') . '</t></is></c>';
        $rowsXml .= '<c r="D5" s="3" t="inlineStr"><is><t>LOKASI GUDANG</t></is></c>';
        $rowsXml .= '<c r="E5" s="4" t="inlineStr"><is><t>' . htmlspecialchars($stockOpname->location_label, ENT_XML1, 'UTF-8') . '</t></is></c>';
        $rowsXml .= '</row>';

        // Row 6: Admin Pemeriksa & Tanggal Selesai
        $rowsXml .= '<row r="6" ht="20" customHeight="1">';
        $rowsXml .= '<c r="A6" s="3" t="inlineStr"><is><t>ADMIN PEMERIKSA</t></is></c>';
        $rowsXml .= '<c r="B6" s="4" t="inlineStr"><is><t>' . htmlspecialchars($adminName, ENT_XML1, 'UTF-8') . '</t></is></c>';
        $rowsXml .= '<c r="D6" s="3" t="inlineStr"><is><t>TANGGAL SELESAI</t></is></c>';
        $rowsXml .= '<c r="E6" s="4" t="inlineStr"><is><t>' . htmlspecialchars($completedDate, ENT_XML1, 'UTF-8') . '</t></is></c>';
        $rowsXml .= '</row>';

        // Row 7: Blank separator
        $rowsXml .= '<row r="7"/>';

        // Row 8: Summary Totals Header
        $rowsXml .= '<row r="8" ht="20" customHeight="1">';
        $rowsXml .= '<c r="A8" s="3" t="inlineStr"><is><t>TOTAL TARGET SISTEM</t></is></c>';
        $rowsXml .= '<c r="C8" s="3" t="inlineStr"><is><t>FISIK SESUAI (MATCH)</t></is></c>';
        $rowsXml .= '<c r="E8" s="3" t="inlineStr"><is><t>BELUM DITEMUKAN (MISSING)</t></is></c>';
        $rowsXml .= '<c r="G8" s="3" t="inlineStr"><is><t>ANOMALI / SALAH LOKASI</t></is></c>';
        $rowsXml .= '</row>';

        // Row 9: Summary Totals Values
        $rowsXml .= '<row r="9" ht="22" customHeight="1">';
        $rowsXml .= '<c r="A9" s="14" t="inlineStr"><is><t>' . (string)$stockOpname->total_system . ' Unit</t></is></c>';
        $rowsXml .= '<c r="C9" s="15" t="inlineStr"><is><t>' . (string)$stockOpname->total_matched . ' Unit</t></is></c>';
        $rowsXml .= '<c r="E9" s="16" t="inlineStr"><is><t>' . (string)$stockOpname->total_missing . ' Unit</t></is></c>';
        $rowsXml .= '<c r="G9" s="17" t="inlineStr"><is><t>' . (string)$stockOpname->total_surplus . ' Unit</t></is></c>';
        $rowsXml .= '</row>';

        // Row 10: Blank separator
        $rowsXml .= '<row r="10"/>';

        // Row 11: Main Table Headers (Exact columns from user request and website)
        $headers = [
            'A' => ['title' => 'NO', 'style' => 5],
            'B' => ['title' => 'NOMOR REGISTRASI', 'style' => 5],
            'C' => ['title' => 'NAMA PERANGKAT', 'style' => 6],
            'D' => ['title' => 'AREA', 'style' => 5],
            'E' => ['title' => 'STATUS DI SISTEM', 'style' => 6],
            'F' => ['title' => 'HASIL CEK FISIK', 'style' => 5],
            'G' => ['title' => 'WAKTU SCAN', 'style' => 5],
            'H' => ['title' => 'CATATAN / KETERANGAN', 'style' => 6],
        ];

        $rowsXml .= '<row r="11" ht="26" customHeight="1">';
        foreach ($headers as $colLetter => $h) {
            $rowsXml .= '<c r="' . $colLetter . '11" s="' . $h['style'] . '" t="inlineStr"><is><t>' . htmlspecialchars($h['title'], ENT_XML1, 'UTF-8') . '</t></is></c>';
        }
        $rowsXml .= '</row>';

        // Row 12+: Items
        $rowNum = 12;
        $no = 1;
        foreach ($stockOpname->items as $item) {
            $statusLabel = match ($item->physical_status) {
                'matched' => 'SESUAI (MATCH)',
                'wrong_location' => 'ANOMALI (SALAH POSISI)',
                'surplus_unregistered' => 'TAK TERDAFTAR',
                default => 'BELUM DITEMUKAN (MISSING)',
            };

            $statusStyle = match ($item->physical_status) {
                'matched' => 10,
                'wrong_location' => 12,
                'surplus_unregistered' => 13,
                default => 11,
            };

            $sysStatus = ucwords(str_replace('_', ' ', $item->system_status ?? 'gudang'));
            if ($item->system_location) {
                $sysStatus .= ' (' . $item->system_location . ')';
            }

            $scanTime = $item->scanned_at ? $item->scanned_at->format('d/m/Y H:i:s') : '-';
            $notes = $item->notes ?: '-';

            $rowsXml .= '<row r="' . $rowNum . '" ht="20" customHeight="1">';
            $rowsXml .= '<c r="A' . $rowNum . '" s="8" t="inlineStr"><is><t>' . $no++ . '</t></is></c>';
            $rowsXml .= '<c r="B' . $rowNum . '" s="9" t="inlineStr"><is><t>' . htmlspecialchars($item->registration_number, ENT_XML1, 'UTF-8') . '</t></is></c>';
            $rowsXml .= '<c r="C' . $rowNum . '" s="7" t="inlineStr"><is><t>' . htmlspecialchars($item->device_name, ENT_XML1, 'UTF-8') . '</t></is></c>';
            $rowsXml .= '<c r="D' . $rowNum . '" s="8" t="inlineStr"><is><t>' . htmlspecialchars($item->area ?? '-', ENT_XML1, 'UTF-8') . '</t></is></c>';
            $rowsXml .= '<c r="E' . $rowNum . '" s="7" t="inlineStr"><is><t>' . htmlspecialchars($sysStatus, ENT_XML1, 'UTF-8') . '</t></is></c>';
            $rowsXml .= '<c r="F' . $rowNum . '" s="' . $statusStyle . '" t="inlineStr"><is><t>' . htmlspecialchars($statusLabel, ENT_XML1, 'UTF-8') . '</t></is></c>';
            $rowsXml .= '<c r="G' . $rowNum . '" s="8" t="inlineStr"><is><t>' . htmlspecialchars($scanTime, ENT_XML1, 'UTF-8') . '</t></is></c>';
            $rowsXml .= '<c r="H' . $rowNum . '" s="7" t="inlineStr"><is><t>' . htmlspecialchars($notes, ENT_XML1, 'UTF-8') . '</t></is></c>';
            $rowsXml .= '</row>';

            $rowNum++;
        }

        $sheetDataXml = '<sheetData>' . $rowsXml . '</sheetData>';

        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
            '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
            '<Default Extension="xml" ContentType="application/xml"/>' .
            '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' .
            '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' .
            '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' .
            '</Types>';

        $rootRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' .
            '</Relationships>';

        $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
            '<sheets><sheet name="Stock Opname" sheetId="1" r:id="rId1"/></sheets>' .
            '</workbook>';

        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' .
            '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' .
            '</Relationships>';

        $worksheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
            '<sheetViews>' .
            '<sheetView tabSelected="1" workbookViewId="0">' .
            '<pane ySplit="11" topLeftCell="A12" activePane="bottomLeft" state="frozen"/>' .
            '</sheetView>' .
            '</sheetViews>' .
            '<cols>' .
            '<col min="1" max="1" width="6" customWidth="1"/>' .
            '<col min="2" max="2" width="26" customWidth="1"/>' .
            '<col min="3" max="3" width="42" customWidth="1"/>' .
            '<col min="4" max="4" width="14" customWidth="1"/>' .
            '<col min="5" max="5" width="28" customWidth="1"/>' .
            '<col min="6" max="6" width="26" customWidth="1"/>' .
            '<col min="7" max="7" width="22" customWidth="1"/>' .
            '<col min="8" max="8" width="28" customWidth="1"/>' .
            '</cols>' .
            $sheetDataXml .
            '</worksheet>';

        $zip->addFromString('[Content_Types].xml', $contentTypes);
        $zip->addFromString('_rels/.rels', $rootRels);
        $zip->addFromString('xl/workbook.xml', $workbook);
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);
        $zip->addFromString('xl/styles.xml', $stylesXml);
        $zip->addFromString('xl/worksheets/sheet1.xml', $worksheet);
        $zip->close();

        $content = file_get_contents($tempFile);
        @unlink($tempFile);

        return $content;
    }
}
