<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DeviceSpecification;
use App\Models\DeviceHistory;
use App\Models\WorkOrder;
use App\Models\WorkOrderEvidence;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\SimpleExcelService;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        $query = Device::with(['currentTechnician', 'activeWorkOrder'])->withCount('evidence')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('registration_number', 'like', "%{$search}%")
                  ->orWhere('kode_spek', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('nojar', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('stock_status')) {
            if (in_array($request->stock_status, ['tersedia_hb_jkt', 'tersedia_pt', 'tersedia_hb_dikta'])) {
                $query->whereIn('stock_status', ['tersedia_hb_jkt', 'tersedia_pt', 'tersedia_hb_dikta']);
            } else {
                $query->where('stock_status', $request->stock_status);
            }
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        if ($request->filled('lintasarta')) {
            if ($request->lintasarta === 'belum') {
                $query->whereNull('returned_to_lintasarta');
            } else {
                $query->where('returned_to_lintasarta', $request->lintasarta);
            }
        }

        if ($request->filled('area')) {
            $query->where('area', $request->area);
        }

        $devices = $query->paginate(10)->withQueryString();

        return view('admin.devices.index', compact('devices'));
    }

    public function checkSpec(Request $request)
    {
        $specCode = trim($request->query('spec', $request->query('code', '')));
        if (empty($specCode)) {
            return response()->json(['found' => false]);
        }

        $spec = DeviceSpecification::where('spec_code', $specCode)->first();

        if (!$spec) {
            $spec = DeviceSpecification::where('spec_code', 'like', "{$specCode}%")->first();
        }

        if (!$spec) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found' => true,
            'spec_code' => $spec->spec_code,
            'name' => $spec->name,
            'brand' => $spec->brand,
            'type' => $spec->type,
        ]);
    }

    public function checkRegistration(Request $request)
    {
        $reg = trim($request->query('reg', ''));
        $raw = trim($request->query('raw', ''));
        if (empty($reg) && empty($raw)) {
            return response()->json(['exists' => false]);
        }

        // Ekstrak kandidat kode unit/registrasi (misal barcode Lintasarta B2WN0262020HA0267 -> HA0267 / HA2067)
        $searchTerms = array_filter([$reg, $raw]);
        $candidates = [];
        foreach ($searchTerms as $term) {
            $candidates[] = $term;
            if (preg_match('/([A-Za-z]{1,3}[-_]?\d{3,6})$/', $term, $m)) {
                $candidates[] = strtoupper($m[1]);
                $candidates[] = strtoupper(str_replace(['-', '_'], '', $m[1]));
            }
            if (strlen($term) > 6) {
                $candidates[] = strtoupper(substr($term, -6));
                $candidates[] = strtoupper(substr($term, -5));
            }
            if (preg_match('/(\d{4,6})$/', $term, $dm)) {
                $candidates[] = $dm[1];
            }
        }
        $candidates = array_unique(array_filter($candidates));

        // 1. Cek exact match terlebih dahulu
        $device = Device::with('currentTechnician')
            ->where(function($q) use ($candidates, $reg) {
                if ($reg) {
                    $q->orWhere('registration_number', $reg)
                      ->orWhere('serial_number', $reg);
                }
                foreach ($candidates as $cand) {
                    $q->orWhere('registration_number', $cand)
                      ->orWhere('serial_number', $cand);
                }
            })
            ->first();

        // 2. Jika belum ketemu, cek partial match
        if (!$device) {
            $device = Device::with('currentTechnician')
                ->where(function($q) use ($candidates, $reg) {
                    if ($reg && strlen($reg) >= 3) {
                        $q->orWhere('registration_number', 'like', "%{$reg}%")
                          ->orWhere('serial_number', 'like', "%{$reg}%");
                    }
                    foreach ($candidates as $cand) {
                        if (strlen($cand) >= 3) {
                            $q->orWhere('registration_number', 'like', "%{$cand}%")
                              ->orWhere('serial_number', 'like', "%{$cand}%");
                        }
                    }
                })
                ->first();
        }

        // 3. Fallback: cek apakah registration_number berakhiran dengan kandidat
        if (!$device) {
            $device = Device::with('currentTechnician')->get()->first(function($d) use ($candidates) {
                foreach ($candidates as $cand) {
                    if (!empty($d->registration_number) && str_ends_with(strtoupper($cand), strtoupper($d->registration_number))) {
                        return true;
                    }
                    if (!empty($d->serial_number) && str_ends_with(strtoupper($cand), strtoupper($d->serial_number))) {
                        return true;
                    }
                }
                return false;
            });
        }

        if (!$device) {
            // Cek apakah ada DeviceSpecification yang cocok (exact spec_code atau prefix dari registration/raw)
            $spec = null;
            if ($reg) {
                $spec = DeviceSpecification::where('spec_code', $reg)->first();
            }
            if (!$spec && $raw) {
                $spec = DeviceSpecification::where('spec_code', $raw)->first();
            }

            if (!$spec) {
                $specsList = DeviceSpecification::orderByRaw('LENGTH(spec_code) DESC')->get();
                foreach ($searchTerms as $term) {
                    $termUpper = strtoupper($term);
                    foreach ($specsList as $candSpec) {
                        if (str_starts_with($termUpper, strtoupper($candSpec->spec_code))) {
                            $spec = $candSpec;
                            break 2;
                        }
                    }
                }
            }

            if ($spec) {
                return response()->json([
                    'exists' => false,
                    'spec_found' => true,
                    'spec_code' => $spec->spec_code,
                    'name' => $spec->name,
                    'brand' => $spec->brand,
                    'type' => $spec->type,
                ]);
            }

            return response()->json(['exists' => false, 'spec_found' => false]);
        }

        return response()->json([
            'exists' => true,
            'id' => $device->id,
            'registration_number' => $device->registration_number,
            'kode_spek' => $device->kode_spek,
            'serial_number' => $device->serial_number,
            'name' => $device->name,
            'brand' => $device->brand,
            'type' => $device->type,
            'area' => $device->area,
            'stock_status' => $device->stock_status,
            'stock_status_label' => $device->stock_status_label,
            'is_in_warehouse' => in_array($device->stock_status, ['tersedia_hb_jkt', 'tersedia_hb_ckr', 'tersedia_pt', 'tersedia_hb_dikta']),
            'current_location' => $device->current_location,
            'current_technician_id' => $device->current_technician_id,
            'current_technician_name' => $device->currentTechnician?->name,
            'activity' => $device->activity,
            'nojar' => $device->nojar,
            'customer_name' => $device->customer_name,
            'address' => $device->address,
            'show_url' => route('admin.devices.show', $device),
        ]);
    }

    public function create(Request $request)
    {
        $suggestedReg = $request->query('reg');
        if ($suggestedReg) {
            if (preg_match('/([A-Za-z]{1,3}[-_]?\d{3,6})$/', $suggestedReg, $m)) {
                $suggestedReg = strtoupper($m[1]);
            }
        } else {
            $suggestedReg = '';
        }

        $suggestedNojar = '';

        $technicians = User::where('role', 'teknisi')->orderBy('name')->get();
        $specs = DeviceSpecification::orderBy('spec_code')->get(['spec_code', 'name', 'brand', 'type']);

        return view('admin.devices.create', compact('suggestedReg', 'suggestedNojar', 'technicians', 'specs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'registration_number' => 'required|string|max:50',
            'kode_spek' => 'nullable|string|max:50',
            'serial_number' => 'nullable|string|max:100',
            'name' => 'required|string|max:150',
            'area' => 'nullable|string|in:BWA,BDA,SEOA,SAST,CDA,NEA',
            'nojar' => 'nullable|string|max:50',
            'customer_name' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'activity' => 'required|in:Survey,PSB,Troubleshoot,Simulasi,Spare Dikta',
            'technician_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        // Simpan / daftarkan ke master spesifikasi jika kode_spek baru
        if (!empty($validated['kode_spek'])) {
            DeviceSpecification::firstOrCreate(
                ['spec_code' => $validated['kode_spek']],
                [
                    'name' => $validated['name'],
                    'brand' => 'PT DIKTA',
                    'type' => 'Lainnya',
                ]
            );
        }

        // Jika aktivitas adalah Spare Dikta, kosongkan nojar, pelanggan, dan alamat
        if ($validated['activity'] === 'Spare Dikta') {
            $validated['nojar'] = null;
            $validated['customer_name'] = null;
            $validated['address'] = null;
        }

        // Tentukan teknisi jika dipilih
        $technician = null;
        if (!empty($validated['technician_id'])) {
            $technician = User::where('role', 'teknisi')->find($validated['technician_id']);
        }

        // Cek apakah nomor registrasi ini sudah pernah didaftarkan / sudah ada di stok
        $existingDevice = Device::where('registration_number', $validated['registration_number'])->first();

        if ($existingDevice) {
            $previousStatus = $existingDevice->stock_status;
            $previousLocation = $existingDevice->current_location;

            $updateData = [
                'kode_spek' => $validated['kode_spek'] ?? $existingDevice->kode_spek,
                'name' => $validated['name'] ?: $existingDevice->name,
                'area' => $validated['area'] ?? $existingDevice->area,
                'activity' => $validated['activity'],
                'nojar' => $validated['nojar'],
                'customer_name' => $validated['customer_name'],
                'address' => $validated['address'],
                'notes' => $validated['notes'] ?? $existingDevice->notes,
            ];

            if ($technician) {
                $updateData['current_technician_id'] = $technician->id;
                $updateData['stock_status'] = 'dibawa_teknisi';
                $updateData['current_location'] = 'Dibawa oleh Teknisi ' . $technician->name;
            } else {
                $updateData['current_technician_id'] = null;
                $targetHub = $request->input('warehouse_hub', 'hb_jkt');
                if ($targetHub === 'hb_ckr' || $request->input('stock_status') === 'tersedia_hb_ckr') {
                    $updateData['stock_status'] = 'tersedia_hb_ckr';
                    $hubName = 'Gudang HB CKR';
                } else {
                    $updateData['stock_status'] = 'tersedia_hb_jkt';
                    $hubName = 'Gudang HB JKT';
                }
                if ($validated['activity'] === 'Spare Dikta') {
                    $updateData['current_location'] = $hubName . ' (Stok Spare)';
                } else {
                    $updateData['current_location'] = !empty($validated['address']) 
                        ? $hubName . ' (Alokasi: ' . $validated['address'] . ')' 
                        : $hubName;
                }
            }

            $existingDevice->update($updateData);

            // Sinkronisasikan otomatis dengan data tiket penugasan WorkOrder jika NOJAR terisi
            if (!empty($validated['nojar'])) {
                $categoryMap = [
                    'Survey' => 'Survei',
                    'PSB' => 'Instalasi',
                    'Troubleshoot' => 'Troubleshooting',
                    'Simulasi' => 'Aktivasi',
                    'Spare Dikta' => 'Instalasi',
                ];

                $coordinator = User::where('role', 'koordinator')->first() ?? auth()->user();

                WorkOrder::updateOrCreate(
                    ['nojar' => $validated['nojar']],
                    [
                        'title' => 'Pekerjaan ' . $validated['activity'] . ' - ' . ($validated['customer_name'] ?? 'Pelanggan ' . $validated['nojar']),
                        'category' => $categoryMap[$validated['activity']] ?? 'Instalasi',
                        'customer_name' => $validated['customer_name'] ?? 'Pelanggan ' . $validated['nojar'],
                        'location_address' => $validated['address'] ?? 'Alamat Belum Ditentukan',
                        'coordinator_id' => $coordinator->id,
                        'technician_id' => $technician ? $technician->id : null,
                        'device_id' => $existingDevice->id,
                        'status' => $technician ? 'sedang_dikerjakan' : 'belum_dikerjakan',
                        'status_notes' => 'Alokasi pemindahan perangkat fisik ' . $existingDevice->registration_number . ' (' . $existingDevice->name . '). ' . 
                                         ($technician ? 'Langsung dialokasikan & dibawa teknisi ' . $technician->name . '. ' : '') . 
                                         ($validated['notes'] ?? ''),
                    ]
                );
            }

            // Catat di Riwayat / Audit Trail
            DeviceHistory::create([
                'device_id' => $existingDevice->id,
                'actor_id' => auth()->id(),
                'technician_id' => $technician ? $technician->id : null,
                'action' => $technician 
                    ? 'Alokasi Stok ke Teknisi (' . $validated['activity'] . ')' 
                    : 'Pembaruan Alokasi Perangkat (' . $validated['activity'] . ')',
                'previous_status' => $previousStatus,
                'new_status' => $existingDevice->stock_status,
                'from_location' => $previousLocation,
                'to_location' => $existingDevice->current_location,
                'notes' => 'Perangkat dari stok dialokasikan kembali untuk aktivitas ' . $validated['activity'] . 
                           ($technician ? ' [Dibawa oleh Teknisi: ' . $technician->name . ']' : '') .
                           ($validated['nojar'] ? ' [NOJAR: ' . $validated['nojar'] . ']' : '') . 
                           ($validated['customer_name'] ? ' [Pelanggan: ' . $validated['customer_name'] . ']' : '') . 
                           '. ' . ($validated['notes'] ?? ''),
            ]);

            return redirect()->route('admin.devices.show', $existingDevice)->with('success', 
                "Perangkat {$existingDevice->registration_number} ({$existingDevice->name}) dari stok berhasil diperbarui" . 
                ($technician ? " dan dialokasikan ke teknisi {$technician->name}." : ".")
            );
        }

        // Jika serial number belum terisi / belum ter-scan, sinkronkan dengan nomor registrasi atau generate kode
        if (empty($validated['serial_number'])) {
            $validated['serial_number'] = $validated['registration_number'] ?? ('SN-' . strtoupper(substr(md5(uniqid()), 0, 10)));
        }

        // Tentukan merk & tipe cerdas berdasarkan nama perangkat
        $nameLower = strtolower($validated['name']);
        if (str_contains($nameLower, 'huawei')) {
            $validated['brand'] = 'Huawei';
        } elseif (str_contains($nameLower, 'zte')) {
            $validated['brand'] = 'ZTE';
        } elseif (str_contains($nameLower, 'fiberhome')) {
            $validated['brand'] = 'Fiberhome';
        } elseif (str_contains($nameLower, 'mikrotik') || str_contains($nameLower, 'router')) {
            $validated['brand'] = 'MikroTik';
        } elseif (str_contains($nameLower, 'tp-link') || str_contains($nameLower, 'cisco')) {
            $validated['brand'] = 'TP-Link';
        } else {
            $validated['brand'] = 'PT DIKTA';
        }

        if (str_contains($nameLower, 'router') || str_contains($nameLower, 'mikrotik')) {
            $validated['type'] = 'Router';
        } elseif (str_contains($nameLower, 'switch')) {
            $validated['type'] = 'Switch';
        } elseif (str_contains($nameLower, 'access point') || str_contains($nameLower, 'ap')) {
            $validated['type'] = 'Access Point';
        } else {
            $validated['type'] = 'ONT';
        }

        $validated['condition'] = 'baru';

        if ($technician) {
            $validated['current_technician_id'] = $technician->id;
            $validated['stock_status'] = 'dibawa_teknisi';
            $validated['current_location'] = 'Dibawa oleh Teknisi ' . $technician->name;
        } else {
            $validated['current_technician_id'] = null;
            $targetHub = $request->input('warehouse_hub', 'hb_jkt');
            if ($targetHub === 'hb_ckr' || $request->input('stock_status') === 'tersedia_hb_ckr') {
                $validated['stock_status'] = 'tersedia_hb_ckr';
                $hubName = 'Gudang HB CKR';
            } else {
                $validated['stock_status'] = 'tersedia_hb_jkt';
                $hubName = 'Gudang HB JKT';
            }
            if ($validated['activity'] === 'Spare Dikta') {
                $validated['current_location'] = $hubName . ' (Stok Spare)';
            } else {
                $validated['current_location'] = !empty($validated['address']) 
                    ? $hubName . ' (Alokasi: ' . $validated['address'] . ')' 
                    : $hubName;
            }
        }

        $device = Device::create($validated);

        // Jika Nojar diisi, sinkronisasikan otomatis dengan data tiket penugasan WorkOrder
        if (!empty($validated['nojar'])) {
            $categoryMap = [
                'Survey' => 'Survei',
                'PSB' => 'Instalasi',
                'Troubleshoot' => 'Troubleshooting',
                'Simulasi' => 'Aktivasi',
            ];

            $coordinator = User::where('role', 'koordinator')->first() ?? auth()->user();

            WorkOrder::updateOrCreate(
                ['nojar' => $validated['nojar']],
                [
                    'title' => 'Pekerjaan ' . $validated['activity'] . ' - ' . ($validated['customer_name'] ?? 'Pelanggan ' . $validated['nojar']),
                    'category' => $categoryMap[$validated['activity']] ?? 'Instalasi',
                    'customer_name' => $validated['customer_name'] ?? 'Pelanggan ' . $validated['nojar'],
                    'location_address' => $validated['address'] ?? 'Alamat Belum Ditentukan',
                    'coordinator_id' => $coordinator->id,
                    'technician_id' => $technician ? $technician->id : null,
                    'device_id' => $device->id,
                    'status' => $technician ? 'sedang_dikerjakan' : 'belum_dikerjakan',
                    'status_notes' => 'Pendaftaran perangkat fisik ' . $device->registration_number . ' (' . $device->name . '). ' . 
                                     ($technician ? 'Langsung dibawa oleh teknisi ' . $technician->name . '. ' : '') . 
                                     ($validated['notes'] ?? ''),
                ]
            );
        }

        // Catat di Riwayat / Audit Trail
        DeviceHistory::create([
            'device_id' => $device->id,
            'actor_id' => auth()->id(),
            'technician_id' => $technician ? $technician->id : null,
            'action' => $technician 
                ? 'Registrasi & Dibawa Teknisi (' . $validated['activity'] . ')' 
                : 'Registrasi Perangkat (' . $validated['activity'] . ')',
            'previous_status' => null,
            'new_status' => $device->stock_status,
            'from_location' => 'Scan Barcode / Input Pendaftaran',
            'to_location' => $device->current_location,
            'notes' => 'Perangkat didaftarkan untuk aktivitas ' . $validated['activity'] . 
                       ($technician ? ' [Dibawa oleh Teknisi: ' . $technician->name . ']' : '') .
                       ($validated['nojar'] ? ' [NOJAR: ' . $validated['nojar'] . ']' : '') . 
                       ($validated['customer_name'] ? ' [Pelanggan: ' . $validated['customer_name'] . ']' : '') . 
                       '. ' . ($validated['notes'] ?? ''),
        ]);

        return redirect()->route('admin.devices.show', $device)->with('success', 
            "Perangkat {$device->registration_number} ({$device->name}) berhasil didaftarkan" . 
            ($technician ? " dan langsung dialokasikan ke teknisi {$technician->name}." : ".")
        );
    }

    public function show(Device $device)
    {
        $device->load(['currentTechnician', 'histories.actor', 'histories.technician', 'histories.workOrder', 'workOrders.coordinator', 'workOrders.technician']);
        $technicians = User::where('role', 'teknisi')->orderBy('name')->get();

        return view('admin.devices.show', compact('device', 'technicians'));
    }

    public function edit(Device $device)
    {
        $technicians = User::where('role', 'teknisi')->orderBy('name')->get();
        $specs = DeviceSpecification::orderBy('spec_code')->get(['spec_code', 'name', 'brand', 'type']);
        return view('admin.devices.edit', compact('device', 'technicians', 'specs'));
    }

    public function update(Request $request, Device $device)
    {
        $validated = $request->validate([
            'registration_number' => ['required', 'string', 'max:50', Rule::unique('devices')->ignore($device->id)],
            'kode_spek' => 'nullable|string|max:50',
            'serial_number' => ['required', 'string', 'max:100', Rule::unique('devices')->ignore($device->id)],
            'name' => 'required|string|max:150',
            'brand' => 'required|string|max:100',
            'model' => 'nullable|string|max:100',
            'type' => 'required|string|max:50',
            'nojar' => 'nullable|string|max:50',
            'customer_name' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'activity' => 'nullable|in:Survey,PSB,Troubleshoot,Simulasi,Spare Dikta',
            'current_technician_id' => 'nullable|exists:users,id',
            'stock_status' => 'required|in:tersedia_hb_jkt,tersedia_hb_ckr,tersedia_pt,tersedia_hb_dikta,dibawa_teknisi,terpasang_pelanggan,terinstal,pending,rusak,hilang,dismantle',
            'condition' => 'required|in:baru,bekas_bagus,rusak',
            'current_location' => 'required|string|max:200',
            'notes' => 'nullable|string',
            'area' => 'nullable|string|in:BWA,BDA,SEOA,SAST,CDA,NEA',
            'returned_to_lintasarta' => 'nullable|string|in:Gudang Tekno,BWA,BDA,SEOA,SAST,CDA,NEA',
        ]);

        if (in_array($validated['stock_status'], ['tersedia_hb_jkt', 'tersedia_hb_ckr', 'tersedia_pt', 'tersedia_hb_dikta'])) {
            $validated['current_technician_id'] = null;
            if ($validated['stock_status'] === 'tersedia_hb_ckr' && (empty($validated['current_location']) || str_contains($validated['current_location'], 'Gudang HB'))) {
                $validated['current_location'] = 'Gudang HB CKR';
            } elseif (in_array($validated['stock_status'], ['tersedia_hb_jkt', 'tersedia_pt', 'tersedia_hb_dikta']) && (empty($validated['current_location']) || str_contains($validated['current_location'], 'Gudang HB'))) {
                $validated['current_location'] = 'Gudang HB JKT';
            }
        } elseif ($validated['stock_status'] !== 'dibawa_teknisi') {
            $validated['current_technician_id'] = null;
        } elseif (!empty($validated['current_technician_id'])) {
            $tech = User::find($validated['current_technician_id']);
            if ($tech && empty($request->filled('current_location'))) {
                $validated['current_location'] = 'Dibawa oleh Teknisi ' . $tech->name;
            }
        }

        $oldStatus = $device->stock_status;
        $oldLocation = $device->current_location;

        $device->update($validated);

        if ($oldStatus !== $device->stock_status || $oldLocation !== $device->current_location) {
            DeviceHistory::create([
                'device_id' => $device->id,
                'actor_id' => auth()->id(),
                'action' => 'Pembaruan Data & Status oleh Admin',
                'previous_status' => $oldStatus,
                'new_status' => $device->stock_status,
                'from_location' => $oldLocation,
                'to_location' => $device->current_location,
                'notes' => 'Perubahan informasi master/status oleh ' . auth()->user()->name,
            ]);
        }

        return redirect()->route('admin.devices.show', $device)->with('success', 'Data perangkat berhasil diperbarui.');
    }

    public function updateLintasarta(Request $request, Device $device)
    {
        $validated = $request->validate([
            'returned_to_lintasarta' => 'nullable|string|in:Gudang Tekno,BWA,BDA,SEOA,SAST,CDA,NEA',
        ]);

        $oldValue = $device->returned_to_lintasarta;
        $newValue = $validated['returned_to_lintasarta'] ?: null;

        $device->update(['returned_to_lintasarta' => $newValue]);

        if ($oldValue !== $newValue) {
            DeviceHistory::create([
                'device_id' => $device->id,
                'actor_id' => auth()->id(),
                'action' => $newValue 
                    ? 'Dikembalikan ke Lintasarta (' . $newValue . ')' 
                    : 'Batal Dikembalikan ke Lintasarta',
                'previous_status' => $device->stock_status,
                'new_status' => $device->stock_status,
                'from_location' => $oldValue ? 'Lintasarta: ' . $oldValue : $device->current_location,
                'to_location' => $newValue ? 'Lintasarta (' . $newValue . ')' : $device->current_location,
                'notes' => $newValue 
                    ? 'Perangkat ditandai telah dikembalikan ke Lintasarta tujuan ' . $newValue . '.' 
                    : 'Tanda pengembalian ke Lintasarta dibatalkan.',
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $newValue 
                    ? 'Perangkat berhasil ditandai: Dikembalikan ke ' . $newValue 
                    : 'Status pengembalian ke Lintasarta dibatalkan',
                'returned_to_lintasarta' => $newValue,
            ]);
        }

        return back()->with('success', 'Status pengembalian ke Lintasarta berhasil diperbarui.');
    }

    public function returnIndex(Request $request)
    {
        $query = Device::with(['currentTechnician'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('registration_number', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('area', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('area')) {
            $query->where('area', $request->area);
        }

        if ($request->filled('stock_status')) {
            if (in_array($request->stock_status, ['tersedia_hb_jkt', 'tersedia_pt', 'tersedia_hb_dikta'])) {
                $query->whereIn('stock_status', ['tersedia_hb_jkt', 'tersedia_pt', 'tersedia_hb_dikta']);
            } else {
                $query->where('stock_status', $request->stock_status);
            }
        }

        $devices = $query->paginate(15)->withQueryString();
        $allDevices = Device::select('id', 'registration_number', 'name', 'brand', 'area', 'stock_status', 'current_location')
            ->orderBy('registration_number')
            ->get();

        return view('admin.devices.returns', compact('devices', 'allDevices'));
    }

    public function processReturn(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|exists:devices,id',
            'destination' => 'required|string|in:Gudang Tekno,BWA,BDA,SEOA,SAST,CDA,NEA',
            'notes' => 'nullable|string|max:500',
        ], [
            'device_id.required' => 'Silakan pilih perangkat yang ingin dikembalikan.',
            'device_id.exists' => 'Perangkat yang dipilih tidak ditemukan.',
            'destination.required' => 'Silakan tentukan tujuan pengembalian ke Lintasarta.',
            'destination.in' => 'Tujuan pengembalian tidak valid.',
        ]);

        $device = Device::findOrFail($validated['device_id']);
        $reg = $device->registration_number;
        $name = $device->name;
        $destination = $validated['destination'];

        // Hapus perangkat secara otomatis sehingga data perangkat langsung hilang dari sistem
        $device->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Perangkat {$reg} ({$name}) telah berhasil dikembalikan ke Lintasarta ({$destination}) dan otomatis data perangkatnya hilang dari sistem.",
                'device_id' => $device->id,
            ]);
        }

        return redirect()->back()->with('success', "Perangkat {$reg} ({$name}) telah berhasil dikembalikan ke Lintasarta ({$destination}) dan otomatis data perangkatnya hilang dari sistem.");
    }

    public function returnToLintasarta(Request $request, Device $device)
    {
        $validated = $request->validate([
            'destination' => 'required|string|in:Gudang Tekno,BWA,BDA,SEOA,SAST,CDA,NEA',
            'notes' => 'nullable|string|max:500',
        ]);

        $reg = $device->registration_number;
        $name = $device->name;
        $destination = $validated['destination'];

        // Hapus perangkat secara otomatis sehingga data perangkat langsung hilang dari sistem
        $device->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Perangkat {$reg} ({$name}) telah berhasil dikembalikan ke Lintasarta ({$destination}) dan otomatis data perangkatnya hilang dari sistem.",
                'device_id' => $device->id,
            ]);
        }

        return back()->with('success', "Perangkat {$reg} ({$name}) telah berhasil dikembalikan ke Lintasarta ({$destination}) dan otomatis data perangkatnya hilang dari sistem.");
    }

    public function updateArea(Request $request, Device $device)
    {
        $validated = $request->validate([
            'area' => 'nullable|string|in:BWA,BDA,SEOA,SAST,CDA,NEA',
        ]);

        $oldValue = $device->area;
        $newValue = $validated['area'] ?: null;

        $device->update(['area' => $newValue]);

        if ($oldValue !== $newValue) {
            DeviceHistory::create([
                'device_id' => $device->id,
                'actor_id' => auth()->id(),
                'action' => $newValue 
                    ? 'Perubahan Area (' . $newValue . ')' 
                    : 'Area Dikosongkan',
                'previous_status' => $device->stock_status,
                'new_status' => $device->stock_status,
                'from_location' => $device->current_location,
                'to_location' => $device->current_location,
                'notes' => 'Perubahan area perangkat menjadi ' . ($newValue ?: 'Kosong') . ' (sebelumnya: ' . ($oldValue ?: '-') . ').',
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $newValue 
                    ? 'Area berhasil diperbarui: ' . $newValue 
                    : 'Area berhasil dikosongkan',
                'area' => $newValue,
            ]);
        }

        return back()->with('success', 'Area berhasil diperbarui.');
    }

    public function destroy(Device $device)
    {
        if ($device->stock_status === 'dibawa_teknisi') {
            return back()->with('error', 'Perangkat tidak dapat dihapus karena saat ini sedang dibawa oleh teknisi!');
        }

        $reg = $device->registration_number;
        $device->delete();

        return redirect()->route('admin.devices.index')->with('success', "Perangkat {$reg} berhasil dihapus dari sistem.");
    }

    public function stock(Request $request)
    {
        $summary = [
            'total' => Device::count(),
            'tersedia_hb_jkt' => Device::whereIn('stock_status', ['tersedia_hb_jkt', 'tersedia_pt', 'tersedia_hb_dikta'])->count(),
            'tersedia_hb_ckr' => Device::where('stock_status', 'tersedia_hb_ckr')->count(),
            'tersedia_pt' => Device::whereIn('stock_status', ['tersedia_hb_jkt', 'tersedia_pt', 'tersedia_hb_dikta'])->count(),
            'tersedia_hb_dikta' => Device::whereIn('stock_status', ['tersedia_hb_jkt', 'tersedia_pt', 'tersedia_hb_dikta'])->count(),
            'dibawa_teknisi' => Device::where('stock_status', 'dibawa_teknisi')->count(),
            'terpasang_pelanggan' => Device::whereIn('stock_status', ['terpasang_pelanggan', 'terinstal'])->count(),
            'pending' => Device::where('stock_status', 'pending')->count(),
            'rusak' => Device::where('stock_status', 'rusak')->count(),
            'hilang' => Device::where('stock_status', 'hilang')->count(),
            'dismantle' => Device::where('stock_status', 'dismantle')->count(),
        ];

        $stockByType = Device::selectRaw('type, 
            count(*) as total, 
            sum(case when stock_status in ("tersedia_hb_jkt", "tersedia_pt", "tersedia_hb_dikta") then 1 else 0 end) as tersedia_jkt,
            sum(case when stock_status = "tersedia_hb_ckr" then 1 else 0 end) as tersedia_ckr,
            sum(case when stock_status in ("tersedia_hb_jkt", "tersedia_hb_ckr", "tersedia_pt", "tersedia_hb_dikta") then 1 else 0 end) as tersedia_pt,
            sum(case when stock_status = "dibawa_teknisi" then 1 else 0 end) as dibawa_teknisi,
            sum(case when stock_status in ("terpasang_pelanggan", "terinstal") then 1 else 0 end) as terpasang_pelanggan,
            sum(case when stock_status = "pending" then 1 else 0 end) as pending,
            sum(case when stock_status = "rusak" then 1 else 0 end) as rusak,
            sum(case when stock_status = "hilang" then 1 else 0 end) as hilang,
            sum(case when stock_status = "dismantle" then 1 else 0 end) as dismantle')
            ->groupBy('type')
            ->get();

        $search = $request->query('search');
        $hub = $request->query('hub');

        $warehouseQuery = Device::whereIn('stock_status', ['tersedia_hb_jkt', 'tersedia_hb_ckr', 'tersedia_pt', 'tersedia_hb_dikta']);
        if ($hub === 'hb_jkt') {
            $warehouseQuery->whereIn('stock_status', ['tersedia_hb_jkt', 'tersedia_pt', 'tersedia_hb_dikta']);
        } elseif ($hub === 'hb_ckr') {
            $warehouseQuery->where('stock_status', 'tersedia_hb_ckr');
        }
        if (!empty($search)) {
            $searchCandidates = [$search];
            if (preg_match('/([A-Za-z]{1,3}\d{3,6})$/', $search, $m)) {
                $searchCandidates[] = strtoupper($m[1]);
            }
            if (strlen($search) > 6) {
                $searchCandidates[] = strtoupper(substr($search, -6));
                $searchCandidates[] = strtoupper(substr($search, -5));
            }
            $searchCandidates = array_unique(array_filter($searchCandidates));

            $warehouseQuery->where(function($q) use ($search, $searchCandidates) {
                foreach ($searchCandidates as $cand) {
                    $q->orWhere('registration_number', 'like', "%{$cand}%")
                      ->orWhere('kode_spek', 'like', "%{$cand}%")
                      ->orWhere('serial_number', 'like', "%{$cand}%");
                }
                $q->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('nojar', 'like', "%{$search}%");
            });
        }
        $devicesInWarehouse = $warehouseQuery->latest('id')->paginate(10)->withQueryString();

        $devicesInWarehouseData = $devicesInWarehouse->map(function($d) {
            return [
                'id' => $d->id,
                'registration_number' => $d->registration_number,
                'kode_spek' => $d->kode_spek ?? '',
                'serial_number' => $d->serial_number,
                'name' => $d->name,
                'brand' => $d->brand,
                'type' => $d->type,
                'area' => $d->area ?? '',
                'customer_name' => $d->customer_name ?? '',
                'address' => $d->address ?? '',
                'nojar' => $d->nojar ?? '',
                'activity' => $d->activity ?? 'PSB',
                'current_location' => $d->current_location ?? ($d->stock_status === 'tersedia_hb_ckr' ? 'Gudang HB CKR' : 'Gudang HB JKT'),
                'stock_status' => $d->stock_status,
                'stock_status_label' => $d->stock_status_label,
                'searchStr' => strtolower(($d->kode_spek ?? '') . ' ' . $d->registration_number . ' ' . $d->serial_number . ' ' . $d->name . ' ' . $d->brand . ' ' . ($d->area ?? '') . ' ' . ($d->customer_name ?? '') . ' ' . ($d->address ?? '') . ' ' . ($d->nojar ?? ''))
            ];
        })->values();

        $devicesInField = Device::with(['currentTechnician', 'activeWorkOrder'])->where('stock_status', 'dibawa_teknisi')->latest()->get();
        $technicians = User::where('role', 'teknisi')->orderBy('name')->get();

        return view('admin.devices.stock', compact('summary', 'stockByType', 'devicesInWarehouse', 'devicesInWarehouseData', 'devicesInField', 'technicians', 'search', 'hub'));
    }

    public function allocateToTechnician(Request $request, Device $device)
    {
        $validated = $request->validate([
            'technician_id' => 'required|exists:users,id',
            'nojar' => 'nullable|string|max:50',
            'customer_name' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'activity' => 'nullable|in:Survey,PSB,Troubleshoot,Simulasi,Spare Dikta',
            'area' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:500',
        ]);

        $technician = User::where('role', 'teknisi')->findOrFail($validated['technician_id']);

        $previousStatus = $device->stock_status;
        $previousLocation = $device->current_location;

        $updateData = [
            'current_technician_id' => $technician->id,
            'stock_status' => 'dibawa_teknisi',
            'current_location' => 'Dibawa oleh Teknisi ' . $technician->name,
        ];

        if (!empty($validated['nojar'])) {
            $updateData['nojar'] = $validated['nojar'];
        }
        if (!empty($validated['activity'])) {
            $updateData['activity'] = $validated['activity'];
        }
        if (!empty($validated['area'])) {
            $updateData['area'] = $validated['area'];
        }
        if (array_key_exists('customer_name', $validated) && $validated['customer_name'] !== null) {
            $updateData['customer_name'] = $validated['customer_name'];
        }
        if (array_key_exists('address', $validated) && $validated['address'] !== null) {
            $updateData['address'] = $validated['address'];
        }

        $device->update($updateData);

        // Jika ada nojar, buat atau update WorkOrder
        $nojar = $device->nojar;
        $hubName = ($previousStatus === 'tersedia_hb_ckr' || str_contains($previousLocation ?? '', 'CKR')) ? 'HB CKR' : 'HB JKT';
        $hubLocation = ($hubName === 'HB CKR') ? 'Gudang HB CKR' : 'Gudang HB JKT';

        if (!empty($nojar)) {
            $activity = $device->activity ?? 'PSB';
            $categoryMap = [
                'Survey' => 'Survei',
                'PSB' => 'Instalasi',
                'Troubleshoot' => 'Troubleshooting',
                'Simulasi' => 'Aktivasi',
            ];

            $coordinator = User::where('role', 'koordinator')->first() ?? auth()->user();

            WorkOrder::updateOrCreate(
                ['nojar' => $nojar],
                [
                    'title' => 'Pekerjaan ' . $activity . ' - ' . ($device->customer_name ?? 'Pelanggan ' . $nojar),
                    'category' => $categoryMap[$activity] ?? 'Instalasi',
                    'customer_name' => $device->customer_name ?? 'Pelanggan ' . $nojar,
                    'location_address' => $device->address ?? 'Alamat Belum Ditentukan',
                    'coordinator_id' => $coordinator->id,
                    'technician_id' => $technician->id,
                    'device_id' => $device->id,
                    'status' => 'sedang_dikerjakan',
                    'status_notes' => 'Alokasi langsung dari stok ' . $hubName . ' ke teknisi ' . $technician->name . '. ' . ($validated['notes'] ?? ''),
                ]
            );
        }

        // Catat di Riwayat / Audit Trail
        DeviceHistory::create([
            'device_id' => $device->id,
            'actor_id' => auth()->id(),
            'technician_id' => $technician->id,
            'action' => 'Alokasi Stok ' . $hubName . ' ke Teknisi',
            'previous_status' => $previousStatus,
            'new_status' => 'dibawa_teknisi',
            'from_location' => $previousLocation ?: $hubLocation,
            'to_location' => 'Dibawa oleh Teknisi ' . $technician->name,
            'notes' => 'Alokasi langsung dari stok ' . $hubName . ' kepada teknisi ' . $technician->name . 
                       ($nojar ? ' [NOJAR: ' . $nojar . ']' : '') . 
                       ($device->customer_name ? ' [Pelanggan: ' . $device->customer_name . ']' : '') . 
                       ($device->address ? ' [Alamat: ' . $device->address . ']' : '') . 
                       '. ' . ($validated['notes'] ?? ''),
        ]);

        return back()->with('success', "Perangkat {$device->registration_number} ({$device->name}) berhasil dialokasikan kepada teknisi {$technician->name}. Status stok kini Dibawa Teknisi.");
    }

    public function printLabel(Device $device)
    {
        return view('admin.devices.print_label', compact('device'));
    }

    public function evidence(Device $device)
    {
        $evidenceList = WorkOrderEvidence::whereHas('workOrder', function ($q) use ($device) {
            $q->where('device_id', $device->id);
        })->with(['workOrder.technician', 'technician'])->latest()->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'photo_url' => asset('storage/' . $item->photo_path),
                'photo_type_label' => $item->photo_type_label,
                'caption' => $item->caption ?: 'Tidak ada deskripsi',
                'technician_name' => $item->technician ? $item->technician->name : ($item->workOrder && $item->workOrder->technician ? $item->workOrder->technician->name : '-'),
                'nojar' => $item->workOrder ? $item->workOrder->nojar : '-',
                'customer_name' => $item->workOrder ? $item->workOrder->customer_name : ($device->customer_name ?: '-'),
                'latitude' => $item->latitude,
                'longitude' => $item->longitude,
                'created_at_formatted' => $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-',
                'created_at_human' => $item->created_at ? $item->created_at->diffForHumans() : '-',
            ];
        });

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'device' => [
                    'id' => $device->id,
                    'registration_number' => $device->registration_number,
                    'name' => $device->name,
                    'brand' => $device->brand,
                    'type' => $device->type,
                    'customer_name' => $device->customer_name,
                    'address' => $device->address,
                ],
                'evidence' => $evidenceList,
            ]);
        }

        return view('admin.devices.evidence', compact('device', 'evidenceList'));
    }

    /**
     * Download sample template for Excel / CSV import.
     */
    public function downloadTemplate(Request $request)
    {
        $format = strtolower($request->query('format', 'xlsx'));

        if ($format === 'csv') {
            $content = SimpleExcelService::generateSampleCsv();
            return response($content, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="Template_Import_Perangkat_PT_DIKTA.csv"',
            ]);
        }

        $content = SimpleExcelService::generateSampleXlsx();
        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="Template_Import_Perangkat_PT_DIKTA.xlsx"',
        ]);
    }

    /**
     * Import devices from uploaded Excel (.xlsx) or CSV (.csv) file.
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
        ], [
            'file.required' => 'Silakan pilih file Excel (.xlsx) atau CSV (.csv) untuk diunggah.',
            'file.max' => 'Ukuran file maksimal 10MB.',
        ]);

        $uploadedFile = $request->file('file');
        $ext = strtolower($uploadedFile->getClientOriginalExtension());
        if (!in_array($ext, ['xlsx', 'csv', 'txt'])) {
            return redirect()->back()->with('error', 'Format file tidak didukung. Harap unggah file .xlsx atau .csv');
        }

        try {
            $rows = SimpleExcelService::parse($uploadedFile->getRealPath(), $ext);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }

        if (empty($rows)) {
            return redirect()->back()->with('error', 'File Excel kosong atau tidak memiliki baris data yang valid.');
        }

        $insertedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;

        $coordinator = User::where('role', 'koordinator')->first() ?? auth()->user();

        foreach ($rows as $rowIndex => $row) {
            $rowNum = $row['_row_number'] ?? ($rowIndex + 2);

            $rawReg = trim((string)($row['registration_number'] ?? $row['no_registrasi'] ?? ''));
            $rawSn = trim((string)($row['serial_number'] ?? ''));
            $rawName = trim((string)($row['name'] ?? $row['nama_perangkat'] ?? ''));
            $kodeSpek = trim((string)($row['kode_spek'] ?? $row['spec_code'] ?? ''));

            // Auto-detect kode spek jika kosong tapi rawReg atau rawSn diawali kode spek
            if (empty($kodeSpek)) {
                $checkTerm = $rawReg ?: $rawSn;
                if (!empty($checkTerm)) {
                    $matchedSpec = DeviceSpecification::orderByRaw('LENGTH(spec_code) DESC')->get()->first(function($s) use ($checkTerm) {
                        return str_starts_with(strtoupper($checkTerm), strtoupper($s->spec_code));
                    });
                    if ($matchedSpec) {
                        $kodeSpek = $matchedSpec->spec_code;
                        if (empty($rawName)) {
                            $rawName = $matchedSpec->name;
                        }
                    }
                }
            } elseif (empty($rawName)) {
                $specItem = DeviceSpecification::where('spec_code', $kodeSpek)->first();
                if ($specItem) {
                    $rawName = $specItem->name;
                }
            }

            // Barcode intelligence: auto-extract 5-6 digit suffix (e.g. B2WN0262020HA0267 -> HA0267)
            $regNumber = $rawReg;
            $serialNumber = $rawSn;

            if ($regNumber !== '' && preg_match('/([A-Za-z]{1,3}\d{3,6})$/', $regNumber, $m)) {
                $extracted = strtoupper($m[1]);
                if (strlen($regNumber) > strlen($extracted)) {
                    if ($serialNumber === '') {
                        $serialNumber = $regNumber;
                    }
                    $regNumber = $extracted;
                }
            } elseif ($regNumber === '' && $serialNumber !== '') {
                if ($serialNumber !== '' && preg_match('/([A-Za-z]{1,3}\d{3,6})$/', $serialNumber, $m)) {
                    $regNumber = strtoupper($m[1]);
                } else {
                    $regNumber = $serialNumber;
                }
            }

            // Fallback jika registration_number tetap kosong
            if ($regNumber === '') {
                if ($rawName !== '') {
                    $lastDevice = Device::latest('id')->first();
                    $nextNum = ($lastDevice ? $lastDevice->id : 0) + $insertedCount + 1;
                    $regNumber = 'REG-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
                } else {
                    $skippedCount++;
                    continue;
                }
            }

            if ($serialNumber === '') {
                $serialNumber = $regNumber;
            }

            $name = $rawName !== '' ? $rawName : ($row['model'] ?? 'Perangkat Jaringan');

            // Brand & Type auto-detect
            $nameLower = strtolower($name);
            $brand = trim((string)($row['brand'] ?? ''));
            if ($brand === '') {
                if (str_contains($nameLower, 'huawei')) {
                    $brand = 'Huawei';
                } elseif (str_contains($nameLower, 'zte')) {
                    $brand = 'ZTE';
                } elseif (str_contains($nameLower, 'fiberhome')) {
                    $brand = 'Fiberhome';
                } elseif (str_contains($nameLower, 'mikrotik') || str_contains($nameLower, 'routerboard')) {
                    $brand = 'MikroTik';
                } elseif (str_contains($nameLower, 'tp-link') || str_contains($nameLower, 'cisco')) {
                    $brand = 'TP-Link';
                } else {
                    $brand = 'PT DIKTA';
                }
            }

            $type = trim((string)($row['type'] ?? ''));
            if ($type === '') {
                if (str_contains($nameLower, 'router') || str_contains($nameLower, 'mikrotik')) {
                    $type = 'Router';
                } elseif (str_contains($nameLower, 'switch')) {
                    $type = 'Switch';
                } elseif (str_contains($nameLower, 'access point') || str_contains($nameLower, 'ap')) {
                    $type = 'Access Point';
                } else {
                    $type = 'ONT';
                }
            }

            $area = strtoupper(trim((string)($row['area'] ?? '')));
            if (!in_array($area, Device::AREAS)) {
                $area = null;
            }

            $nojar = trim((string)($row['nojar'] ?? '')) ?: null;
            $customerName = trim((string)($row['customer_name'] ?? '')) ?: null;
            $address = trim((string)($row['address'] ?? '')) ?: null;
            $notes = trim((string)($row['notes'] ?? '')) ?: null;
            $model = trim((string)($row['model'] ?? '')) ?: null;

            $activity = trim((string)($row['activity'] ?? ''));
            $validActivities = ['Survey', 'PSB', 'Troubleshoot', 'Simulasi', 'Spare Dikta'];
            $normalizedActivity = 'Spare Dikta';
            foreach ($validActivities as $va) {
                if (strcasecmp($activity, $va) === 0) {
                    $normalizedActivity = $va;
                    break;
                }
            }
            if ($activity === '' && ($nojar !== null || $customerName !== null)) {
                $normalizedActivity = 'PSB';
            }

            // Normalisasi status stok
            $rawStatus = strtolower(trim((string)($row['stock_status'] ?? '')));
            if (str_contains($rawStatus, 'ckr') || str_contains($rawStatus, 'cikarang')) {
                $stockStatus = 'tersedia_hb_ckr';
                $defaultHubLocation = 'Gudang HB CKR';
            } else {
                $stockStatus = 'tersedia_hb_jkt';
                $defaultHubLocation = 'Gudang HB JKT';
            }
            if (str_contains($rawStatus, 'teknisi')) {
                $stockStatus = 'dibawa_teknisi';
            } elseif (str_contains($rawStatus, 'terinstal') || str_contains($rawStatus, 'terpasang')) {
                $stockStatus = 'terpasang_pelanggan';
            } elseif (str_contains($rawStatus, 'rusak')) {
                $stockStatus = 'rusak';
            } elseif (str_contains($rawStatus, 'hilang')) {
                $stockStatus = 'hilang';
            } elseif (str_contains($rawStatus, 'dismantle')) {
                $stockStatus = 'dismantle';
            } elseif (str_contains($rawStatus, 'pending')) {
                $stockStatus = 'pending';
            }

            // Normalisasi kondisi
            $rawCond = strtolower(trim((string)($row['condition'] ?? '')));
            $condition = 'baru';
            if (str_contains($rawCond, 'bekas') || str_contains($rawCond, 'second')) {
                $condition = 'bekas_bagus';
            } elseif (str_contains($rawCond, 'rusak')) {
                $condition = 'rusak';
            }

            $currentLocation = $defaultHubLocation;
            if ($stockStatus === 'dibawa_teknisi') {
                $currentLocation = 'Dibawa Teknisi';
            } elseif ($stockStatus === 'terpasang_pelanggan' && $address) {
                $currentLocation = 'Lokasi Pelanggan: ' . $address;
            } elseif ($normalizedActivity === 'Spare Dikta') {
                $currentLocation = $defaultHubLocation . ' (Stok Spare)';
            }

            // Upsert Device
            $existing = Device::where('registration_number', $regNumber)->first();

            if ($existing) {
                $prevStatus = $existing->stock_status;
                $prevLocation = $existing->current_location;

                $existing->update([
                    'kode_spek' => $kodeSpek ?: $existing->kode_spek,
                    'serial_number' => $serialNumber ?: $existing->serial_number,
                    'name' => $name ?: $existing->name,
                    'brand' => $brand ?: $existing->brand,
                    'model' => $model ?: $existing->model,
                    'type' => $type ?: $existing->type,
                    'area' => $area ?: $existing->area,
                    'nojar' => $nojar ?: $existing->nojar,
                    'customer_name' => $customerName ?: $existing->customer_name,
                    'address' => $address ?: $existing->address,
                    'activity' => $normalizedActivity,
                    'stock_status' => $stockStatus,
                    'condition' => $condition,
                    'notes' => $notes ?: $existing->notes,
                    'current_location' => $currentLocation,
                ]);

                DeviceHistory::create([
                    'device_id' => $existing->id,
                    'actor_id' => auth()->id(),
                    'action' => 'Pembaruan Data via Import Excel',
                    'previous_status' => $prevStatus,
                    'new_status' => $stockStatus,
                    'from_location' => $prevLocation,
                    'to_location' => $currentLocation,
                    'notes' => "Diimpor dari file {$uploadedFile->getClientOriginalName()} (Baris {$rowNum})",
                ]);

                $updatedCount++;
            } else {
                $newDevice = Device::create([
                    'registration_number' => $regNumber,
                    'kode_spek' => $kodeSpek ?: null,
                    'serial_number' => $serialNumber,
                    'name' => $name,
                    'brand' => $brand,
                    'model' => $model,
                    'type' => $type,
                    'area' => $area,
                    'nojar' => $nojar,
                    'customer_name' => $customerName,
                    'address' => $address,
                    'activity' => $normalizedActivity,
                    'stock_status' => $stockStatus,
                    'condition' => $condition,
                    'notes' => $notes,
                    'current_location' => $currentLocation,
                ]);

                DeviceHistory::create([
                    'device_id' => $newDevice->id,
                    'actor_id' => auth()->id(),
                    'action' => 'Registrasi Baru via Import Excel',
                    'previous_status' => null,
                    'new_status' => $stockStatus,
                    'from_location' => null,
                    'to_location' => $currentLocation,
                    'notes' => "Ditambahkan melalui import Excel dari file {$uploadedFile->getClientOriginalName()} (Baris {$rowNum})",
                ]);

                $insertedCount++;
            }

            // Sync WorkOrder jika ada NOJAR
            if ($nojar) {
                $categoryMap = [
                    'Survey' => 'Survei',
                    'PSB' => 'Instalasi',
                    'Troubleshoot' => 'Troubleshooting',
                    'Simulasi' => 'Aktivasi',
                    'Spare Dikta' => 'Instalasi',
                ];

                WorkOrder::updateOrCreate(
                    ['nojar' => $nojar],
                    [
                        'title' => 'Pekerjaan ' . $normalizedActivity . ' - ' . ($customerName ?? 'Pelanggan ' . $nojar),
                        'category' => $categoryMap[$normalizedActivity] ?? 'Instalasi',
                        'customer_name' => $customerName ?? 'Pelanggan ' . $nojar,
                        'location_address' => $address ?? 'Alamat Belum Ditentukan',
                        'coordinator_id' => $coordinator->id,
                        'device_id' => ($existing ?? $newDevice)->id,
                        'status' => 'belum_dikerjakan',
                        'status_notes' => 'Diimpor dari file Excel ' . $uploadedFile->getClientOriginalName(),
                    ]
                );
            }
        }

        $msg = "Import Excel Berhasil! {$insertedCount} perangkat baru ditambahkan, {$updatedCount} perangkat diperbarui.";
        if ($skippedCount > 0) {
            $msg .= " ({$skippedCount} baris kosong dilewati).";
        }

        return redirect()->back()->with('success', $msg);
    }
}

