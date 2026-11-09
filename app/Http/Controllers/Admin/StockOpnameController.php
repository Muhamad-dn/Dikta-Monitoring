<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DeviceHistory;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StockOpnameController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $status = $request->input('status');

        $query = StockOpname::with('conductor')->orderBy('created_at', 'desc');

        if ($year) {
            $query->where('period_year', $year);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $opnames = $query->paginate(10)->withQueryString();

        $stats = [
            'total_completed' => StockOpname::where('status', 'completed')->count(),
            'total_in_progress' => StockOpname::where('status', 'in_progress')->count(),
            'last_opname' => StockOpname::where('status', 'completed')->latest('completed_at')->first(),
        ];

        $activeOpname = StockOpname::where('status', 'in_progress')->latest()->first();

        return view('admin.stock_opname.index', compact('opnames', 'stats', 'year', 'status', 'activeOpname'));
    }

    public function start(Request $request)
    {
        $activeOpname = StockOpname::where('status', 'in_progress')->latest()->first();
        if ($activeOpname) {
            return redirect()->route('admin.stock-opname.show', $activeOpname)
                ->with('info', "Melanjutkan sesi Stock Opname aktif: {$activeOpname->title}");
        }

        $month = (int)date('n');
        $year = (int)date('Y');
        $location = $request->input('location', 'hb_jkt');
        $monthName = StockOpname::MONTHS[$month] ?? 'Bulan ' . $month;
        $locationLabel = StockOpname::LOCATIONS[$location] ?? 'Gudang HB JKT';
        $title = "Stock Opname {$monthName} {$year} ({$locationLabel})";
        $code = 'SO-' . $year . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . strtoupper(Str::random(4));

        DB::beginTransaction();
        try {
            $opname = StockOpname::create([
                'opname_code' => $code,
                'title' => $title,
                'period_month' => $month,
                'period_year' => $year,
                'location' => $location,
                'status' => 'in_progress',
                'conducted_by' => auth()->id(),
            ]);

            $deviceQuery = Device::query();
            if ($location === 'hb_jkt') {
                $deviceQuery->whereIn('stock_status', ['tersedia_hb_jkt', 'tersedia_pt']);
            } elseif ($location === 'hb_ckr') {
                $deviceQuery->where('stock_status', 'tersedia_hb_ckr');
            } else {
                $deviceQuery->where(function ($q) {
                    $q->whereNull('returned_to_lintasarta')->orWhere('returned_to_lintasarta', false);
                });
            }

            $devices = $deviceQuery->get();
            $itemsData = [];
            $now = now();
            foreach ($devices as $device) {
                $itemsData[] = [
                    'stock_opname_id' => $opname->id,
                    'device_id' => $device->id,
                    'registration_number' => $device->registration_number,
                    'device_name' => $device->name,
                    'area' => $device->area,
                    'system_status' => $device->stock_status,
                    'system_location' => $device->current_location,
                    'physical_status' => 'missing',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!empty($itemsData)) {
                foreach (array_chunk($itemsData, 200) as $chunk) {
                    StockOpnameItem::insert($chunk);
                }
            }

            $opname->recalculateTotals();
            DB::commit();

            return redirect()->route('admin.stock-opname.show', $opname)
                ->with('success', "Sesi {$opname->title} berhasil dimulai! Halaman scan siap digunakan.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memulai stock opname: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'period_month' => 'required|integer|between:1,12',
            'period_year' => 'required|integer|between:2020,2099',
            'location' => 'required|in:semua,hb_jkt,hb_ckr',
            'title' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $monthName = StockOpname::MONTHS[$validated['period_month']] ?? 'Bulan ' . $validated['period_month'];
        $locationLabel = StockOpname::LOCATIONS[$validated['location']] ?? 'Gudang';

        $title = !empty($validated['title']) 
            ? $validated['title'] 
            : "Stock Opname {$monthName} {$validated['period_year']} ({$locationLabel})";

        $code = 'SO-' . $validated['period_year'] . str_pad($validated['period_month'], 2, '0', STR_PAD_LEFT) . '-' . strtoupper(Str::random(4));

        DB::beginTransaction();
        try {
            $opname = StockOpname::create([
                'opname_code' => $code,
                'title' => $title,
                'period_month' => $validated['period_month'],
                'period_year' => $validated['period_year'],
                'location' => $validated['location'],
                'status' => 'in_progress',
                'notes' => $validated['notes'] ?? null,
                'conducted_by' => auth()->id(),
            ]);

            // Ambil snapshot perangkat sesuai lokasi
            $deviceQuery = Device::query();
            if ($validated['location'] === 'hb_jkt') {
                $deviceQuery->whereIn('stock_status', ['tersedia_hb_jkt', 'tersedia_pt']);
            } elseif ($validated['location'] === 'hb_ckr') {
                $deviceQuery->where('stock_status', 'tersedia_hb_ckr');
            } else {
                // Semua perangkat aktif (tidak termasuk yang sudah retur permanen ke lintasarta)
                $deviceQuery->where(function ($q) {
                    $q->whereNull('returned_to_lintasarta')->orWhere('returned_to_lintasarta', false);
                });
            }

            $devices = $deviceQuery->get();

            $itemsData = [];
            $now = now();
            foreach ($devices as $device) {
                $itemsData[] = [
                    'stock_opname_id' => $opname->id,
                    'device_id' => $device->id,
                    'registration_number' => $device->registration_number,
                    'device_name' => $device->name,
                    'area' => $device->area,
                    'system_status' => $device->stock_status,
                    'system_location' => $device->current_location,
                    'physical_status' => 'missing', // default belum di-scan
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!empty($itemsData)) {
                // Bulk insert dalam chunk 200 baris
                foreach (array_chunk($itemsData, 200) as $chunk) {
                    StockOpnameItem::insert($chunk);
                }
            }

            $opname->recalculateTotals();

            DB::commit();

            return redirect()->route('admin.stock-opname.show', $opname)
                ->with('success', "Sesi {$opname->title} berhasil dibuat dengan total target {$opname->total_system} perangkat.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat sesi stock opname: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Request $request, StockOpname $stockOpname)
    {
        $stockOpname->load(['conductor']);

        $tab = $request->input('tab', 'all'); // all, matched, missing, anomaly
        $search = trim($request->input('search', ''));

        $itemsQuery = $stockOpname->items()->with(['device', 'scanner'])->latest('scanned_at');

        if ($tab === 'matched') {
            $itemsQuery->where('physical_status', 'matched');
        } elseif ($tab === 'missing') {
            $itemsQuery->where('physical_status', 'missing');
        } elseif ($tab === 'anomaly') {
            $itemsQuery->whereIn('physical_status', ['wrong_location', 'surplus_unregistered']);
        }

        if (!empty($search)) {
            $keyword = '%' . strtolower($search) . '%';
            $itemsQuery->where(function ($q) use ($keyword) {
                $q->whereRaw('LOWER(registration_number) LIKE ?', [$keyword])
                  ->orWhereRaw('LOWER(device_name) LIKE ?', [$keyword])
                  ->orWhereRaw('LOWER(COALESCE(area, "")) LIKE ?', [$keyword]);
            });
        }

        $items = $itemsQuery->paginate(30)->withQueryString();

        // Rekap tab count
        $counts = [
            'all' => $stockOpname->items()->count(),
            'matched' => $stockOpname->items()->where('physical_status', 'matched')->count(),
            'missing' => $stockOpname->items()->where('physical_status', 'missing')->count(),
            'anomaly' => $stockOpname->items()->whereIn('physical_status', ['wrong_location', 'surplus_unregistered'])->count(),
        ];

        return view('admin.stock_opname.show', compact('stockOpname', 'items', 'tab', 'search', 'counts'));
    }

    public function scan(Request $request, StockOpname $stockOpname)
    {
        if ($stockOpname->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Sesi Stock Opname ini sudah selesai dan terkunci. Tidak dapat memindai lagi.',
            ], 422);
        }

        $code = trim($request->input('code', ''));
        if (empty($code)) {
            return response()->json([
                'success' => false,
                'message' => 'Kode barcode / Nomor Registrasi tidak boleh kosong.',
            ], 422);
        }

        $lowerCode = strtolower($code);

        // 1. Cek apakah ada di daftar target sesi opname ini
        $item = StockOpnameItem::where('stock_opname_id', $stockOpname->id)
            ->where(function ($q) use ($lowerCode) {
                $q->whereRaw('LOWER(registration_number) = ?', [$lowerCode])
                  ->orWhereHas('device', function ($dq) use ($lowerCode) {
                      $dq->whereRaw('LOWER(serial_number) = ?', [$lowerCode])
                        ->orWhereRaw('LOWER(nojar) = ?', [$lowerCode]);
                  });
            })
            ->first();

        if ($item) {
            if ($item->physical_status === 'matched') {
                return response()->json([
                    'success' => false,
                    'already_scanned' => true,
                    'message' => "Perangkat '{$item->registration_number}' sudah di-scan sebelumnya pada " . ($item->scanned_at ? $item->scanned_at->format('H:i:s') : '-'),
                    'item' => $item,
                ]);
            }

            // Tandai cocok (matched)
            $item->update([
                'physical_status' => 'matched',
                'scanned_at' => now(),
                'scanned_by' => auth()->id(),
            ]);

            $stockOpname->recalculateTotals();

            return response()->json([
                'success' => true,
                'type' => 'matched',
                'message' => "Perangkat {$item->registration_number} ({$item->device_name}) terverifikasi ADA!",
                'item' => [
                    'id' => $item->id,
                    'registration_number' => $item->registration_number,
                    'device_name' => $item->device_name,
                    'area' => $item->area ?? '-',
                    'physical_status' => $item->physical_status,
                    'scanned_at' => $item->scanned_at->format('H:i:s d/m/Y'),
                    'badge' => $item->status_badge,
                ],
                'stats' => [
                    'total_system' => $stockOpname->total_system,
                    'total_matched' => $stockOpname->total_matched,
                    'total_missing' => $stockOpname->total_missing,
                    'total_surplus' => $stockOpname->total_surplus,
                    'total_physical' => $stockOpname->total_physical,
                ],
            ]);
        }

        // 2. Jika tidak ada di target sesi ini, cek apakah perangkat terdaftar di sistem secara umum
        $device = Device::whereRaw('LOWER(registration_number) = ?', [$lowerCode])
            ->orWhereRaw('LOWER(serial_number) = ?', [$lowerCode])
            ->orWhereRaw('LOWER(nojar) = ?', [$lowerCode])
            ->first();

        if ($device) {
            // Perangkat ada di sistem tapi tidak seharusnya di gudang ini (misal di teknisi atau di HB CKR)
            $anomalyItem = StockOpnameItem::create([
                'stock_opname_id' => $stockOpname->id,
                'device_id' => $device->id,
                'registration_number' => $device->registration_number,
                'device_name' => $device->name,
                'area' => $device->area,
                'system_status' => $device->stock_status,
                'system_location' => $device->current_location,
                'physical_status' => 'wrong_location',
                'scanned_at' => now(),
                'scanned_by' => auth()->id(),
                'notes' => "Fisik ditemukan di " . $stockOpname->location_label . ", status sistem: " . $device->stock_status,
            ]);

            $stockOpname->recalculateTotals();

            return response()->json([
                'success' => true,
                'type' => 'anomaly',
                'message' => "Peringatan Anomali: Perangkat '{$device->registration_number}' fisik ditemukan di sini, tapi di sistem tercatat '{$device->stock_status}'!",
                'item' => [
                    'id' => $anomalyItem->id,
                    'registration_number' => $anomalyItem->registration_number,
                    'device_name' => $anomalyItem->device_name,
                    'area' => $anomalyItem->area ?? '-',
                    'physical_status' => $anomalyItem->physical_status,
                    'scanned_at' => $anomalyItem->scanned_at->format('H:i:s d/m/Y'),
                    'badge' => $anomalyItem->status_badge,
                ],
                'stats' => [
                    'total_system' => $stockOpname->total_system,
                    'total_matched' => $stockOpname->total_matched,
                    'total_missing' => $stockOpname->total_missing,
                    'total_surplus' => $stockOpname->total_surplus,
                    'total_physical' => $stockOpname->total_physical,
                ],
            ]);
        }

        // 3. Tidak ada di sistem sama sekali
        return response()->json([
            'success' => false,
            'not_found' => true,
            'message' => "Barcode / Nomor '{$code}' TIDAK TERDAFTAR di sistem!",
        ], 404);
    }

    public function resolveDiscrepancy(Request $request, StockOpname $stockOpname, StockOpnameItem $item)
    {
        if ($item->stock_opname_id !== $stockOpname->id) {
            abort(404);
        }

        if ($stockOpname->status === 'completed') {
            return back()->with('error', 'Sesi opname sudah selesai dan dikunci.');
        }

        if ($item->device) {
            $device = $item->device;
            $oldStatus = $device->stock_status;
            $targetStatus = ($stockOpname->location === 'hb_ckr') ? 'tersedia_hb_ckr' : 'tersedia_hb_jkt';
            $targetLocation = ($stockOpname->location === 'hb_ckr') ? 'Gudang HB Cikarang' : 'Gudang HB Jakarta';

            $device->update([
                'stock_status' => $targetStatus,
                'current_technician_id' => null,
                'current_location' => $targetLocation,
            ]);

            DeviceHistory::create([
                'device_id' => $device->id,
                'actor_id' => auth()->id(),
                'action' => 'Koreksi Posisi Fisik Hasil Stock Opname',
                'previous_status' => $oldStatus,
                'new_status' => $targetStatus,
                'notes' => "Perangkat dipindahkan ke {$targetLocation} sesuai hasil temuan fisik Stock Opname {$stockOpname->title}.",
            ]);

            $item->update([
                'physical_status' => 'matched',
                'notes' => 'Status telah disesuaikan ke ' . $targetLocation,
            ]);

            $stockOpname->recalculateTotals();

            return back()->with('success', "Status perangkat {$device->registration_number} berhasil disinkronkan ke {$targetLocation}.");
        }

        return back()->with('error', 'Perangkat tidak ditemukan di database.');
    }

    public function complete(Request $request, StockOpname $stockOpname)
    {
        $stockOpname->recalculateTotals();

        $stockOpname->update([
            'status' => 'completed',
            'completed_at' => now(),
            'notes' => $request->input('notes', $stockOpname->notes),
        ]);

        return redirect()->route('admin.stock-opname.show', $stockOpname)
            ->with('success', "Stock Opname {$stockOpname->title} telah resmi diselesaikan dan dikunci.");
    }

    public function export(StockOpname $stockOpname): StreamedResponse
    {
        $fileName = 'Stock_Opname_' . Str::slug($stockOpname->title) . '_' . date('Ymd_His') . '.csv';

        $stockOpname->load(['items.device', 'items.scanner']);

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($stockOpname) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM so Excel opens indonesian characters properly
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header info
            fputcsv($handle, ['BERITA ACARA LAPORAN STOCK OPNAME']);
            fputcsv($handle, ['Kode Sesi', $stockOpname->opname_code]);
            fputcsv($handle, ['Judul', $stockOpname->title]);
            fputcsv($handle, ['Periode', $stockOpname->month_name . ' ' . $stockOpname->period_year]);
            fputcsv($handle, ['Lokasi', $stockOpname->location_label]);
            fputcsv($handle, ['Status', $stockOpname->status === 'completed' ? 'Selesai' : 'Sedang Berjalan']);
            fputcsv($handle, ['Total Target Sistem', $stockOpname->total_system]);
            fputcsv($handle, ['Total Fisik Ditemukan (Match)', $stockOpname->total_matched]);
            fputcsv($handle, ['Total Belum Ditemukan (Missing)', $stockOpname->total_missing]);
            fputcsv($handle, ['Total Anomali / Temuan Baru', $stockOpname->total_surplus]);
            fputcsv($handle, ['Tanggal Selesai', $stockOpname->completed_at ? $stockOpname->completed_at->format('d/m/Y H:i') : '-']);
            fputcsv($handle, []);

            // Data Table Headers
            fputcsv($handle, [
                'No',
                'Nomor Registrasi',
                'Nama Perangkat',
                'Area',
                'Status di Sistem',
                'Lokasi di Sistem',
                'Hasil Cek Fisik',
                'Waktu Scan',
                'Petugas Scan',
                'Catatan / Temuan',
            ]);

            $no = 1;
            foreach ($stockOpname->items as $item) {
                $statusText = match ($item->physical_status) {
                    'matched' => 'SESUAI (MATCH)',
                    'wrong_location' => 'ANOMALI (SALAH POSISI)',
                    'surplus_unregistered' => 'TAK TERDAFTAR',
                    default => 'BELUM DITEMUKAN (MISSING)',
                };

                fputcsv($handle, [
                    $no++,
                    $item->registration_number,
                    $item->device_name,
                    $item->area ?? '-',
                    $item->system_status ?? '-',
                    $item->system_location ?? '-',
                    $statusText,
                    $item->scanned_at ? $item->scanned_at->format('d/m/Y H:i:s') : '-',
                    $item->scanner ? $item->scanner->name : '-',
                    $item->notes ?? '-',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function print(StockOpname $stockOpname)
    {
        $stockOpname->load(['conductor', 'items.device', 'items.scanner']);
        return view('admin.stock_opname.print', compact('stockOpname'));
    }

    public function destroy(StockOpname $stockOpname)
    {
        $title = $stockOpname->title;
        $stockOpname->delete();

        return redirect()->route('admin.stock-opname.index')
            ->with('success', "Sesi {$title} berhasil dihapus.");
    }
}
