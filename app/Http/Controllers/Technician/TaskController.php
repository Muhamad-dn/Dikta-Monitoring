<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\Device;
use App\Models\DeviceSpecification;
use App\Models\DeviceHistory;
use App\Models\WorkOrderEvidence;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function show(WorkOrder $task)
    {
        $this->authorizeTask($task);

        $task->load(['device', 'coordinator', 'evidence', 'histories']);

        return view('technician.tasks.show', compact('task'));
    }

    public function scanPickup(WorkOrder $task)
    {
        $this->authorizeTask($task);

        if ($task->status !== 'belum_dikerjakan') {
            return redirect()->route('technician.tasks.show', $task)
                ->with('info', 'Perangkat untuk pekerjaan ini sudah diambil sebelumnya.');
        }

        if (!$task->device) {
            return redirect()->route('technician.tasks.show', $task)
                ->with('error', 'Pekerjaan ini belum ditentukan perangkatnya oleh Koordinator.');
        }

        return view('technician.tasks.scan_pickup', compact('task'));
    }

    public function confirmPickup(Request $request, WorkOrder $task)
    {
        $this->authorizeTask($task);

        $request->validate([
            'registration_number' => 'required|string',
            'pickup_photo' => 'nullable|image|max:5120',
            'notes' => 'nullable|string|max:500',
        ]);

        $scannedCode = trim($request->registration_number);
        $assignedDevice = $task->device;

        if (!$assignedDevice) {
            return back()->with('error', 'Pekerjaan ini tidak memiliki perangkat yang ditugaskan.');
        }

        // Verifikasi apakah kode sesuai dengan nomor registrasi atau nomor seri perangkat yang ditugaskan
        if (strcasecmp($assignedDevice->registration_number, $scannedCode) !== 0 && 
            strcasecmp($assignedDevice->serial_number, $scannedCode) !== 0) {
            return back()->withInput()->with('error', 
                "NOMOR PERANGKAT TIDAK COCOK! Anda memasukkan [{$scannedCode}], sedangkan yang ditugaskan Koordinator adalah [{$assignedDevice->registration_number} - {$assignedDevice->name}]. Mohon periksa kembali fisik perangkat di gudang PT."
            );
        }

        // Update Device
        $oldLocation = $assignedDevice->current_location;
        $assignedDevice->stock_status = 'dibawa_teknisi';
        $assignedDevice->current_technician_id = auth()->id();
        $assignedDevice->current_location = 'Dibawa Teknisi ' . auth()->user()->name . ' (Menuju ' . $task->customer_name . ')';
        $assignedDevice->save();

        // Update Work Order
        $task->status = 'perangkat_diambil';
        $task->pickup_time = now();
        $task->save();

        // Simpan Bukti Foto Pengambilan jika ada
        if ($request->hasFile('pickup_photo')) {
            $path = $request->file('pickup_photo')->store('evidence/' . $task->nojar, 'public');
            WorkOrderEvidence::create([
                'work_order_id' => $task->id,
                'technician_id' => auth()->id(),
                'photo_type' => 'pengambilan_pt',
                'photo_path' => $path,
                'caption' => 'Bukti pengambilan fisik perangkat ' . $assignedDevice->registration_number . ' di gudang PT',
            ]);
        }

        // Catat Audit Trail
        DeviceHistory::create([
            'device_id' => $assignedDevice->id,
            'work_order_id' => $task->id,
            'technician_id' => auth()->id(),
            'actor_id' => auth()->id(),
            'action' => 'Pengambilan Perangkat di Gudang PT',
            'previous_status' => 'tersedia_pt',
            'new_status' => 'dibawa_teknisi',
            'from_location' => $oldLocation,
            'to_location' => $assignedDevice->current_location,
            'notes' => 'Scan validasi berhasil. ' . ($request->notes ? "Catatan: {$request->notes}" : 'Perangkat sah dibawa teknisi.'),
        ]);

        return redirect()->route('technician.tasks.show', $task)->with('success', 
            "Validasi Berhasil! Perangkat {$assignedDevice->registration_number} ({$assignedDevice->name}) telah tercatat resmi Anda bawa."
        );
    }

    public function updateFlow(Request $request, WorkOrder $task)
    {
        $this->authorizeTask($task);

        $action = $request->input('action');
        $device = $task->device;

        switch ($action) {
            case 'start_journey':
                $task->status = 'dalam_perjalanan';
                $task->save();

                if ($device) {
                    $device->current_location = 'Dalam Perjalanan ke Alamat Pelanggan (' . $task->location_address . ')';
                    $device->save();

                    DeviceHistory::create([
                        'device_id' => $device->id,
                        'work_order_id' => $task->id,
                        'technician_id' => auth()->id(),
                        'actor_id' => auth()->id(),
                        'action' => 'Menuju Alamat Lokasi Pekerjaan',
                        'previous_status' => 'dibawa_teknisi',
                        'new_status' => 'dibawa_teknisi',
                        'from_location' => 'Kantor PT DIKTA',
                        'to_location' => $device->current_location,
                        'notes' => 'Teknisi berangkat menuju lokasi pekerjaan ' . $task->nojar,
                    ]);
                }
                $message = 'Status diperbarui: Anda sedang dalam perjalanan ke lokasi.';
                break;

            case 'arrived':
                $task->status = 'sampai_lokasi';
                $task->arrival_time = now();
                $task->save();

                if ($device) {
                    $device->current_location = $task->location_address . ' (' . $task->customer_name . ')';
                    $device->save();

                    DeviceHistory::create([
                        'device_id' => $device->id,
                        'work_order_id' => $task->id,
                        'technician_id' => auth()->id(),
                        'actor_id' => auth()->id(),
                        'action' => 'Tiba di Alamat Lokasi Pekerjaan',
                        'previous_status' => 'dibawa_teknisi',
                        'new_status' => 'dibawa_teknisi',
                        'from_location' => 'Dalam Perjalanan',
                        'to_location' => $device->current_location,
                        'notes' => 'Teknisi telah tiba di alamat pelanggan ' . $task->customer_name,
                    ]);
                }
                $message = 'Status diperbarui: Anda telah sampai di lokasi pekerjaan.';
                break;

            case 'start_work':
                $task->status = 'sedang_dikerjakan';
                $task->save();

                if ($device) {
                    DeviceHistory::create([
                        'device_id' => $device->id,
                        'work_order_id' => $task->id,
                        'technician_id' => auth()->id(),
                        'actor_id' => auth()->id(),
                        'action' => 'Mulai Pengerjaan Lapangan',
                        'previous_status' => 'dibawa_teknisi',
                        'new_status' => 'dibawa_teknisi',
                        'from_location' => $device->current_location,
                        'to_location' => $device->current_location,
                        'notes' => 'Pemasangan/perbaikan sedang aktif berlangsung.',
                    ]);
                }
                $message = 'Status diperbarui: Pengerjaan sedang berlangsung.';
                break;

            default:
                return back()->with('error', 'Aksi alur tidak valid.');
        }

        return redirect()->route('technician.tasks.show', $task)->with('success', $message);
    }

    public function showComplete(WorkOrder $task)
    {
        $this->authorizeTask($task);

        return view('technician.tasks.complete', compact('task'));
    }

    public function submitComplete(Request $request, WorkOrder $task)
    {
        $this->authorizeTask($task);

        $request->validate([
            'status' => 'required|in:terinstal,selesai,pending,gagal_install',
            'status_notes' => 'required|string|max:1000',
            'evidence_photo' => 'nullable|image|max:10240',
            'evidence_photo_base64' => 'nullable|string',
            'evidence_photo_1' => 'nullable|image|max:5120',
            'evidence_photo_2' => 'nullable|image|max:5120',
            'evidence_photo_3' => 'nullable|image|max:5120',
        ]);

        $status = $request->status;
        $device = $task->device;

        $task->status = $status;
        $task->status_notes = $request->status_notes;

        if (in_array($status, ['terinstal', 'selesai'])) {
            $task->completion_time = now();
        }
        $task->save();

        // 1. Simpan foto evidence tunggal (Direct Photo / File Upload)
        if ($request->hasFile('evidence_photo')) {
            $path = $request->file('evidence_photo')->store('evidence/' . $task->nojar, 'public');
            
            $photoType = match ($status) {
                'terinstal', 'selesai' => 'hasil_instalasi',
                'pending', 'gagal_install' => 'kendala',
                default => 'hasil_instalasi',
            };

            $caption = match ($status) {
                'terinstal', 'selesai' => 'Foto Bukti Pemasangan Terinstal',
                'pending' => 'Foto Bukti Kendala Pending / Tertunda',
                'gagal_install' => 'Foto Bukti Gagal Install',
                default => 'Foto Bukti Lapangan',
            };

            WorkOrderEvidence::create([
                'work_order_id' => $task->id,
                'technician_id' => auth()->id(),
                'photo_type' => $photoType,
                'photo_path' => $path,
                'caption' => $caption,
            ]);
        } elseif ($request->filled('evidence_photo_base64')) {
            // 2. Simpan foto jika diambil via Live Camera Webcam (Base64)
            $base64Data = $request->input('evidence_photo_base64');
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data)) {
                $data = substr($base64Data, strpos($base64Data, ',') + 1);
                $data = base64_decode($data);
                $fileName = 'camera_' . time() . '_' . uniqid() . '.jpg';
                $filePath = 'evidence/' . $task->nojar . '/' . $fileName;
                \Illuminate\Support\Facades\Storage::disk('public')->put($filePath, $data);

                $photoType = match ($status) {
                    'terinstal', 'selesai' => 'hasil_instalasi',
                    'pending', 'gagal_install' => 'kendala',
                    default => 'hasil_instalasi',
                };

                $caption = match ($status) {
                    'terinstal', 'selesai' => 'Foto Bukti Pemasangan Terinstal',
                    'pending' => 'Foto Bukti Kendala Pending / Tertunda',
                    'gagal_install' => 'Foto Bukti Gagal Install',
                    default => 'Foto Bukti Lapangan',
                };

                WorkOrderEvidence::create([
                    'work_order_id' => $task->id,
                    'technician_id' => auth()->id(),
                    'photo_type' => $photoType,
                    'photo_path' => $filePath,
                    'caption' => $caption,
                ]);
            }
        }

        // Fallback untuk backward-compatibility foto 1, 2, 3
        $legacyPhotos = [
            ['input' => 'evidence_photo_1', 'type' => 'hasil_instalasi', 'caption' => 'Foto Hasil Pemasangan'],
            ['input' => 'evidence_photo_2', 'type' => 'redaman_ont', 'caption' => 'Foto Nilai Redaman / Pengukuran'],
            ['input' => 'evidence_photo_3', 'type' => 'kendala', 'caption' => 'Foto Bukti Kondisi / Kendala'],
        ];

        foreach ($legacyPhotos as $photo) {
            if ($request->hasFile($photo['input'])) {
                $path = $request->file($photo['input'])->store('evidence/' . $task->nojar, 'public');
                WorkOrderEvidence::create([
                    'work_order_id' => $task->id,
                    'technician_id' => auth()->id(),
                    'photo_type' => $photo['type'],
                    'photo_path' => $path,
                    'caption' => $photo['caption'],
                ]);
            }
        }

        // Update status perangkat jika ada
        if ($device) {
            if (in_array($status, ['terinstal', 'selesai'])) {
                $device->stock_status = 'terpasang_pelanggan';
                $device->current_technician_id = null; // Sudah lepas dari tanggung jawab bawaan teknisi
                $device->current_location = $task->location_address . ' (' . $task->customer_name . ')';
                $device->save();

                DeviceHistory::create([
                    'device_id' => $device->id,
                    'work_order_id' => $task->id,
                    'technician_id' => auth()->id(),
                    'actor_id' => auth()->id(),
                    'action' => 'Terinstal di Pelanggan',
                    'previous_status' => 'dibawa_teknisi',
                    'new_status' => 'terpasang_pelanggan',
                    'from_location' => 'Dibawa Teknisi ' . auth()->user()->name,
                    'to_location' => $device->current_location,
                    'notes' => 'Pekerjaan selesai sukses. ' . $request->status_notes,
                ]);
            } elseif ($status === 'pending') {
                $device->stock_status = 'pending';
                $device->current_location = 'Pending: ' . $task->customer_name . ' (Pegang Teknisi ' . auth()->user()->name . ')';
                $device->save();

                DeviceHistory::create([
                    'device_id' => $device->id,
                    'work_order_id' => $task->id,
                    'technician_id' => auth()->id(),
                    'actor_id' => auth()->id(),
                    'action' => 'Pekerjaan Tertunda (Pending)',
                    'previous_status' => 'dibawa_teknisi',
                    'new_status' => 'pending',
                    'from_location' => $device->current_location,
                    'to_location' => $device->current_location,
                    'notes' => 'Alasan pending: ' . $request->status_notes . '. Perangkat dalam status Pending dipegang teknisi.',
                ]);
            } elseif ($status === 'gagal_install') {
                DeviceHistory::create([
                    'device_id' => $device->id,
                    'work_order_id' => $task->id,
                    'technician_id' => auth()->id(),
                    'actor_id' => auth()->id(),
                    'action' => 'Pekerjaan Gagal Install',
                    'previous_status' => 'dibawa_teknisi',
                    'new_status' => 'dibawa_teknisi',
                    'from_location' => $device->current_location,
                    'to_location' => $device->current_location,
                    'notes' => 'Alasan gagal: ' . $request->status_notes . '. Perangkat perlu dikembalikan ke gudang HB Dikta.',
                ]);
            }
        }

        return redirect()->route('technician.tasks.show', $task)->with('success', 
            "Laporan pekerjaan berhasil diperbarui dengan status: {$task->status_label}."
        );
    }

    public function myDevices()
    {
        $technician = auth()->user();

        $devices = Device::with(['activeWorkOrder'])
            ->where('current_technician_id', $technician->id)
            ->whereIn('stock_status', ['dibawa_teknisi', 'pending'])
            ->latest('updated_at')
            ->get();

        $otherTechnicians = User::where('role', 'teknisi')
            ->where('id', '!=', $technician->id)
            ->orderBy('name')
            ->get();

        return view('technician.devices.index', compact('devices', 'otherTechnicians'));
    }

    public function returnDevice(Request $request, Device $device)
    {
        if ((int)$device->current_technician_id !== (int)auth()->id()) {
            abort(403, 'Anda bukan teknisi pemegang perangkat ini.');
        }

        $request->validate([
            'destination' => 'nullable|string|in:Gudang HB JKT,Gudang HB CKR,Gudang HB Dikta,Gudang Tekno,BWA,BDA,SEOA,SAST,CDA,NEA',
            'condition' => 'required|in:baru,bekas_bagus,rusak',
            'return_notes' => 'nullable|string|max:500',
        ]);

        $destination = $request->input('destination', 'Gudang HB JKT');
        $oldStatus = $device->stock_status;
        $oldLocation = $device->current_location;

        if ($destination === 'Gudang HB Dikta') {
            $device->stock_status = ($request->condition === 'rusak') ? 'rusak' : 'tersedia_pt';
            $device->condition = $request->condition;
            $device->current_technician_id = null;
            $device->current_location = 'Gudang HB Dikta';
            $device->save();

            DeviceHistory::create([
                'device_id' => $device->id,
                'technician_id' => auth()->id(),
                'actor_id' => auth()->id(),
                'action' => 'Pengembalian Perangkat ke HB Dikta',
                'previous_status' => $oldStatus,
                'new_status' => $device->stock_status,
                'from_location' => $oldLocation,
                'to_location' => 'Gudang HB Dikta',
                'notes' => 'Dikembalikan oleh teknisi ' . auth()->user()->name . '. ' . ($request->return_notes ? "Catatan: {$request->return_notes}" : ''),
            ]);

            return back()->with('success', "Perangkat {$device->registration_number} telah berhasil dikembalikan ke Gudang HB Dikta.");
        } elseif ($destination === 'Gudang HB JKT') {
            $device->stock_status = ($request->condition === 'rusak') ? 'rusak' : 'tersedia_hb_jkt';
            $device->condition = $request->condition;
            $device->current_technician_id = null;
            $device->current_location = 'Gudang HB JKT';
            $device->save();

            DeviceHistory::create([
                'device_id' => $device->id,
                'technician_id' => auth()->id(),
                'actor_id' => auth()->id(),
                'action' => 'Pengembalian Perangkat ke HB JKT',
                'previous_status' => $oldStatus,
                'new_status' => $device->stock_status,
                'from_location' => $oldLocation,
                'to_location' => 'Gudang HB JKT',
                'notes' => 'Dikembalikan oleh teknisi ' . auth()->user()->name . '. ' . ($request->return_notes ? "Catatan: {$request->return_notes}" : ''),
            ]);

            return back()->with('success', "Perangkat {$device->registration_number} telah berhasil dikembalikan ke Gudang HB JKT.");
        } elseif ($destination === 'Gudang HB CKR') {
            $device->stock_status = ($request->condition === 'rusak') ? 'rusak' : 'tersedia_hb_ckr';
            $device->condition = $request->condition;
            $device->current_technician_id = null;
            $device->current_location = 'Gudang HB CKR';
            $device->save();

            DeviceHistory::create([
                'device_id' => $device->id,
                'technician_id' => auth()->id(),
                'actor_id' => auth()->id(),
                'action' => 'Pengembalian Perangkat ke HB CKR',
                'previous_status' => $oldStatus,
                'new_status' => $device->stock_status,
                'from_location' => $oldLocation,
                'to_location' => 'Gudang HB CKR',
                'notes' => 'Dikembalikan oleh teknisi ' . auth()->user()->name . '. ' . ($request->return_notes ? "Catatan: {$request->return_notes}" : ''),
            ]);

            return back()->with('success', "Perangkat {$device->registration_number} telah berhasil dikembalikan ke Gudang HB CKR.");
        } else {
            $device->stock_status = ($request->condition === 'rusak') ? 'rusak' : 'tersedia_pt';
            $device->condition = $request->condition;
            $device->returned_to_lintasarta = $destination;
            $device->current_technician_id = null;
            $device->current_location = 'Dikembalikan ke Lintasarta (' . $destination . ')';
            $device->save();

            DeviceHistory::create([
                'device_id' => $device->id,
                'technician_id' => auth()->id(),
                'actor_id' => auth()->id(),
                'action' => 'Pengembalian Perangkat ke Lintasarta (' . $destination . ')',
                'previous_status' => $oldStatus,
                'new_status' => $device->stock_status,
                'from_location' => $oldLocation,
                'to_location' => 'Lintasarta - ' . $destination,
                'notes' => 'Dikembalikan oleh teknisi ' . auth()->user()->name . ' ke Lintasarta (' . $destination . '). ' . ($request->return_notes ? "Catatan: {$request->return_notes}" : ''),
            ]);

            return back()->with('success', "Perangkat {$device->registration_number} telah berhasil dikembalikan ke Lintasarta ({$destination}).");
        }
    }

    public function handoverDevice(Request $request, Device $device)
    {
        if ((int)$device->current_technician_id !== (int)auth()->id()) {
            abort(403, 'Anda bukan teknisi pemegang perangkat ini.');
        }

        $request->validate([
            'to_technician_id' => 'required|exists:users,id|different:current_technician_id',
            'notes' => 'nullable|string|max:500',
        ], [
            'to_technician_id.required' => 'Pilih teknisi tujuan untuk handover.',
            'to_technician_id.different' => 'Teknisi tujuan tidak boleh diri Anda sendiri.',
        ]);

        $newTechnician = User::where('role', 'teknisi')->findOrFail($request->to_technician_id);
        $oldTechName = auth()->user()->name;

        $oldStatus = $device->stock_status;
        $oldLocation = $device->current_location;

        $device->current_technician_id = $newTechnician->id;
        $device->current_location = 'Dibawa Teknisi ' . $newTechnician->name;
        $device->save();

        // Jika ada WorkOrder aktif yang mengikat perangkat ini, reassign ke teknisi baru
        $activeWo = $device->activeWorkOrder;
        if ($activeWo) {
            $activeWo->technician_id = $newTechnician->id;
            $activeWo->save();
        }

        DeviceHistory::create([
            'device_id' => $device->id,
            'technician_id' => $newTechnician->id,
            'actor_id' => auth()->id(),
            'action' => 'Handover ke Teknisi Lain',
            'previous_status' => $oldStatus,
            'new_status' => $device->stock_status,
            'from_location' => $oldLocation,
            'to_location' => $device->current_location,
            'notes' => 'Handover dari Teknisi ' . $oldTechName . ' ke Teknisi ' . $newTechnician->name . '. ' . ($request->notes ? "Catatan: {$request->notes}" : ''),
        ]);

        return back()->with('success', "Perangkat {$device->registration_number} ({$device->name}) berhasil di-handover ke Teknisi {$newTechnician->name}.");
    }

    public function reportBroken(Request $request, Device $device)
    {
        if ((int)$device->current_technician_id !== (int)auth()->id()) {
            abort(403, 'Anda bukan teknisi pemegang perangkat ini.');
        }

        $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        $oldStatus = $device->stock_status;
        $oldLocation = $device->current_location;

        $device->stock_status = 'rusak';
        $device->condition = 'rusak';
        $device->current_location = 'HB Dikta (Area Rusak - Dari ' . auth()->user()->name . ')';
        $device->current_technician_id = null;
        $device->save();

        DeviceHistory::create([
            'device_id' => $device->id,
            'technician_id' => auth()->id(),
            'actor_id' => auth()->id(),
            'action' => 'Pelaporan Unit Rusak oleh Teknisi',
            'previous_status' => $oldStatus,
            'new_status' => 'rusak',
            'from_location' => $oldLocation,
            'to_location' => $device->current_location,
            'notes' => 'Dilaporkan rusak oleh teknisi ' . auth()->user()->name . '. Catatan: ' . $request->notes,
        ]);

        return back()->with('success', 
            "Perangkat {$device->registration_number} telah dilaporkan sebagai RUSAK dan masuk ke rekap Dashboard Admin."
        );
    }

    public function reportLost(Request $request, Device $device)
    {
        if ((int)$device->current_technician_id !== (int)auth()->id()) {
            abort(403, 'Anda bukan teknisi pemegang perangkat ini.');
        }

        $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        $oldStatus = $device->stock_status;
        $oldLocation = $device->current_location;

        $device->stock_status = 'hilang';
        $device->current_location = 'Hilang saat penugasan teknisi (' . auth()->user()->name . ')';
        $device->current_technician_id = null;
        $device->save();

        DeviceHistory::create([
            'device_id' => $device->id,
            'technician_id' => auth()->id(),
            'actor_id' => auth()->id(),
            'action' => 'Pelaporan Unit Hilang oleh Teknisi',
            'previous_status' => $oldStatus,
            'new_status' => 'hilang',
            'from_location' => $oldLocation,
            'to_location' => $device->current_location,
            'notes' => 'Dilaporkan hilang oleh teknisi ' . auth()->user()->name . '. Kronologi: ' . $request->notes,
        ]);

        return back()->with('warning', 
            "Perangkat {$device->registration_number} telah dilaporkan HILANG dan tercatat di Dashboard Admin untuk investigasi."
        );
    }

    public function createDevice(Request $request)
    {
        $suggestedReg = $request->query('reg', '');
        $suggestedNojar = $request->query('nojar', '');
        $technicians = User::where('role', 'teknisi')->orderBy('name')->get();
        $specs = DeviceSpecification::orderBy('spec_code')->get(['spec_code', 'name', 'brand', 'type']);

        return view('technician.devices.create', compact('suggestedReg', 'suggestedNojar', 'technicians', 'specs'));
    }

    public function storeDevice(Request $request)
    {
        $validated = $request->validate([
            'registration_number' => 'required|string|max:100',
            'kode_spek' => 'nullable|string|max:50',
            'serial_number' => 'nullable|string|max:100',
            'name' => 'required|string|max:150',
            'area' => 'nullable|string|in:BWA,BDA,SEOA,SAST,CDA,NEA',
            'nojar' => 'nullable|string|max:50',
            'customer_name' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'activity' => 'required|in:Instalasi Baru,Ganti Perangkat / Rusak,Upgrade Perangkat,Troubleshoot,Simulasi,Spare Dikta,Survey,PSB',
            'technician_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

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

        if ($validated['activity'] === 'Spare Dikta') {
            $validated['nojar'] = null;
            $validated['customer_name'] = null;
            $validated['address'] = null;
        }

        $techId = !empty($validated['technician_id']) ? $validated['technician_id'] : auth()->id();
        $technician = User::where('role', 'teknisi')->find($techId);

        $existingDevice = Device::where('registration_number', $validated['registration_number'])
            ->orWhere('serial_number', $validated['registration_number'])
            ->first();

        if ($existingDevice) {
            $previousStatus = $existingDevice->stock_status;
            $previousLocation = $existingDevice->current_location;

            $updateData = [
                'kode_spek' => $validated['kode_spek'] ?? $existingDevice->kode_spek,
                'name' => $validated['name'] ?: $existingDevice->name,
                'area' => $validated['area'] ?? ($existingDevice->area ?: 'BWA'),
                'activity' => $validated['activity'],
                'nojar' => $validated['nojar'],
                'customer_name' => $validated['customer_name'],
                'address' => $validated['address'],
                'notes' => $validated['notes'] ?? $existingDevice->notes,
            ];

            if ($validated['activity'] === 'Spare Dikta') {
                $updateData['current_technician_id'] = null;
                $updateData['stock_status'] = 'tersedia_hb_jkt';
                $updateData['current_location'] = 'Gudang HB JKT (Stok Spare)';
            } else {
                $updateData['current_technician_id'] = $technician ? $technician->id : auth()->id();
                $updateData['stock_status'] = 'dibawa_teknisi';
                $updateData['current_location'] = 'Dibawa oleh Teknisi ' . ($technician ? $technician->name : auth()->user()->name);
            }

            if (!empty($validated['serial_number'])) {
                $updateData['serial_number'] = $validated['serial_number'];
            }

            $existingDevice->update($updateData);
            $device = $existingDevice;

            DeviceHistory::create([
                'device_id' => $device->id,
                'technician_id' => $device->current_technician_id,
                'actor_id' => auth()->id(),
                'action' => 'Pendaftaran / Alokasi Ulang Perangkat oleh Teknisi',
                'previous_status' => $previousStatus,
                'new_status' => $device->stock_status,
                'from_location' => $previousLocation,
                'to_location' => $device->current_location,
                'notes' => 'Didaftarkan ulang oleh teknisi ' . auth()->user()->name . '. Aktivitas: ' . $validated['activity'],
            ]);
        } else {
            $serialNumber = $validated['serial_number'] ?: ('SN-' . strtoupper(substr(md5(uniqid()), 0, 8)));
            $brand = 'Lainnya';
            if (stripos($validated['name'], 'Cisco') !== false) $brand = 'Cisco';
            elseif (stripos($validated['name'], 'Huawei') !== false) $brand = 'Huawei';
            elseif (stripos($validated['name'], 'ZTE') !== false) $brand = 'ZTE';
            elseif (stripos($validated['name'], 'Fiberhome') !== false) $brand = 'Fiberhome';
            elseif (stripos($validated['name'], 'Raisecom') !== false || stripos($validated['name'], 'RAISEECOM') !== false) $brand = 'Raisecom';
            elseif (stripos($validated['name'], 'Fortinet') !== false) $brand = 'Fortinet';
            elseif (stripos($validated['name'], 'Cambium') !== false) $brand = 'Cambium';

            $stockStatus = ($validated['activity'] === 'Spare Dikta') ? 'tersedia_hb_jkt' : 'dibawa_teknisi';
            $currentTechId = ($validated['activity'] === 'Spare Dikta') ? null : ($technician ? $technician->id : auth()->id());
            $location = ($validated['activity'] === 'Spare Dikta') 
                ? 'Gudang HB JKT (Stok Spare)' 
                : 'Dibawa oleh Teknisi ' . ($technician ? $technician->name : auth()->user()->name);

            $device = Device::create([
                'registration_number' => $validated['registration_number'],
                'kode_spek' => $validated['kode_spek'] ?? null,
                'serial_number' => $serialNumber,
                'name' => $validated['name'],
                'brand' => $brand,
                'model' => $validated['name'],
                'type' => 'ONT',
                'area' => $validated['area'] ?? 'BWA',
                'activity' => $validated['activity'],
                'nojar' => $validated['nojar'],
                'customer_name' => $validated['customer_name'],
                'address' => $validated['address'],
                'stock_status' => $stockStatus,
                'current_technician_id' => $currentTechId,
                'current_location' => $location,
                'condition' => 'baru',
                'notes' => $validated['notes'],
            ]);

            DeviceHistory::create([
                'device_id' => $device->id,
                'technician_id' => $currentTechId,
                'actor_id' => auth()->id(),
                'action' => 'Pendaftaran Perangkat Baru oleh Teknisi',
                'previous_status' => null,
                'new_status' => $stockStatus,
                'from_location' => 'Pendaftaran Baru',
                'to_location' => $location,
                'notes' => 'Didaftarkan oleh teknisi ' . auth()->user()->name . '. Aktivitas: ' . $validated['activity'],
            ]);
        }

        // Jika ada nojar dan bukan spare dikta, buat work order jika belum ada
        if (!empty($validated['nojar']) && $validated['activity'] !== 'Spare Dikta') {
            $categoryMap = [
                'Instalasi Baru' => 'Instalasi',
                'PSB' => 'Instalasi',
                'Survey' => 'Survei',
                'Ganti Perangkat / Rusak' => 'Troubleshooting',
                'Upgrade Perangkat' => 'Instalasi',
                'Troubleshoot' => 'Troubleshooting',
                'Simulasi' => 'Instalasi',
            ];
            $woCategory = $categoryMap[$validated['activity']] ?? 'Instalasi';

            $wo = WorkOrder::where('nojar', $validated['nojar'])->first();
            $coordinator = User::where('role', 'koordinator')->first() ?? auth()->user();
            if (!$wo) {
                WorkOrder::create([
                    'nojar' => $validated['nojar'],
                    'title' => $validated['activity'] . ' - ' . ($validated['customer_name'] ?: $device->name),
                    'category' => $woCategory,
                    'customer_name' => $validated['customer_name'] ?: 'Pelanggan ' . $validated['nojar'],
                    'customer_phone' => '-',
                    'location_address' => $validated['address'] ?: 'Alamat belum diatur',
                    'coordinator_id' => $coordinator->id,
                    'technician_id' => $device->current_technician_id ?: auth()->id(),
                    'device_id' => $device->id,
                    'status' => 'perangkat_diambil',
                    'status_notes' => 'Dibuat otomatis dari pendaftaran perangkat oleh teknisi.',
                    'pickup_time' => now(),
                ]);
            } else {
                $wo->device_id = $device->id;
                $wo->technician_id = $device->current_technician_id ?: auth()->id();
                $wo->save();
            }
        }

        return redirect()->route('technician.dashboard')->with('success', "Perangkat {$device->registration_number} ({$device->name}) berhasil didaftarkan!");
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

        $searchTerms = array_filter([$reg, $raw]);

        $device = Device::where(function($q) use ($reg, $raw) {
            if ($reg) {
                $q->orWhere('registration_number', $reg)
                  ->orWhere('serial_number', $reg)
                  ->orWhere('registration_number', 'like', "%{$reg}%")
                  ->orWhere('serial_number', 'like', "%{$reg}%");
            }
            if ($raw) {
                $q->orWhere('registration_number', $raw)
                  ->orWhere('serial_number', $raw)
                  ->orWhere('registration_number', 'like', "%{$raw}%")
                  ->orWhere('serial_number', 'like', "%{$raw}%");
            }
        })->first();

        if (!$device) {
            // Cek apakah kode_spek cocok dengan reg atau raw
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
            'area' => $device->area,
            'stock_status' => $device->stock_status,
            'current_location' => $device->current_location,
            'activity' => $device->activity,
            'nojar' => $device->nojar,
            'customer_name' => $device->customer_name,
            'address' => $device->address,
        ]);
    }

    public function updateFieldStatus(Request $request)
    {
        // Fallback jika dikirim via input device_code
        if (!$request->filled('device_id') && $request->filled('device_code')) {
            $code = trim($request->device_code);
            $foundDev = Device::where('registration_number', $code)
                ->orWhere('serial_number', $code)
                ->first();
            if ($foundDev) {
                // Jika belum dipegang oleh teknisi ini, alokasikan otomatis
                if (!$foundDev->current_technician_id) {
                    $foundDev->current_technician_id = auth()->id();
                    $foundDev->stock_status = 'dibawa_teknisi';
                    $foundDev->save();
                }
                $request->merge(['device_id' => $foundDev->id]);
            }
        }

        $validated = $request->validate([
            'device_id' => 'required|exists:devices,id',
            'status' => 'required|string|in:SAMPAI LOKASI,TERINSTAL,PENDING,GAGAL INSTAL,DIBAWA KEMBALI KE HB JKT,DIBAWA KEMBALI KE HB CKR,DIBAWA KEMBALI KE HB DIKTA,DI BAWA KEMBALI KE AREA,RUSAK,HILANG',
            'nojar' => 'nullable|string|max:50',
            'current_location' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'evidence_photo' => 'nullable|image|max:10240',
            'evidence_photo_base64' => 'nullable|string',
        ], [
            'device_id.required' => 'Silakan pilih atau masukkan kode perangkat yang ingin di-update statusnya.',
            'status.required' => 'Silakan tentukan status lapangan perangkat.',
            'current_location.required' => 'NOJAR Lokasi Aktual / Pelanggan wajib diisi.',
        ]);

        $device = Device::findOrFail($validated['device_id']);

        if ((int)$device->current_technician_id !== (int)auth()->id()) {
            abort(403, 'Anda bukan teknisi pemegang perangkat ini.');
        }

        $oldStatus = $device->stock_status;
        $oldLocation = $device->current_location;
        $status = $validated['status'];
        $lokasiAktual = trim($validated['current_location']);
        $catatan = trim($validated['notes'] ?? '');
        $nojar = trim($validated['nojar'] ?? ($device->nojar ?? ''));

        if ($request->filled('address')) {
            $device->address = trim($request->address);
        }
        $device->customer_name = $lokasiAktual;

        // Handle Upload Foto Bukti
        $photoPath = null;
        if ($request->hasFile('evidence_photo')) {
            $folder = $nojar ? 'evidence/' . $nojar : 'evidence/technician_updates';
            $photoPath = $request->file('evidence_photo')->store($folder, 'public');
        } elseif ($request->filled('evidence_photo_base64')) {
            $base64Data = $request->input('evidence_photo_base64');
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data)) {
                $data = substr($base64Data, strpos($base64Data, ',') + 1);
                $data = base64_decode($data);
                $fileName = 'update_' . time() . '_' . uniqid() . '.jpg';
                $folder = $nojar ? 'evidence/' . $nojar : 'evidence/technician_updates';
                $photoPath = $folder . '/' . $fileName;
                Storage::disk('public')->put($photoPath, $data);
            }
        }

        // Tentukan perubahan Device berdasarkan status pilihan
        switch ($status) {
            case 'SAMPAI LOKASI':
                $device->stock_status = 'dibawa_teknisi';
                $device->current_location = $lokasiAktual;
                $actionLabel = 'Sampai di Lokasi';
                break;

            case 'TERINSTAL':
                $device->stock_status = 'terinstal';
                $device->current_technician_id = null; // lepas dari bawaan fisik
                $device->current_location = 'Terinstal di: ' . $lokasiAktual;
                $actionLabel = 'Perangkat Terinstal';
                break;

            case 'PENDING':
                $device->stock_status = 'pending';
                $device->current_location = 'Pending: ' . $lokasiAktual;
                $actionLabel = 'Pekerjaan Pending';
                break;

            case 'GAGAL INSTAL':
                $device->stock_status = 'dibawa_teknisi';
                $device->current_location = 'Gagal Instal: ' . $lokasiAktual;
                $actionLabel = 'Gagal Instalasi';
                break;

            case 'DIBAWA KEMBALI KE HB JKT':
            case 'DIBAWA KEMBALI KE HB DIKTA':
                $device->stock_status = 'tersedia_hb_jkt';
                $device->current_technician_id = null;
                $device->current_location = 'Gudang HB JKT';
                $actionLabel = 'Dibawa Kembali ke HB JKT';
                break;

            case 'DIBAWA KEMBALI KE HB CKR':
                $device->stock_status = 'tersedia_hb_ckr';
                $device->current_technician_id = null;
                $device->current_location = 'Gudang HB CKR';
                $actionLabel = 'Dibawa Kembali ke HB CKR';
                break;

            case 'DI BAWA KEMBALI KE AREA':
                $device->stock_status = 'tersedia_pt';
                $device->current_technician_id = null;
                $device->current_location = 'Gudang Area: ' . ($device->area ?: 'BWA');
                $actionLabel = 'Di Bawa Kembali ke Area';
                break;

            case 'RUSAK':
                $device->stock_status = 'rusak';
                $device->condition = 'rusak';
                $device->current_technician_id = null;
                $device->current_location = 'HB Dikta (Area Rusak - Dari ' . auth()->user()->name . ')';
                $actionLabel = 'Unit Rusak di Lapangan';
                break;

            case 'HILANG':
                $device->stock_status = 'hilang';
                $device->current_technician_id = null;
                $device->current_location = 'Hilang saat penugasan teknisi (' . auth()->user()->name . ')';
                $actionLabel = 'Unit Hilang di Lapangan';
                break;
        }

        if (!empty($nojar)) {
            $device->nojar = $nojar;
        }
        $device->save();

        // Update WorkOrder jika ada keterkaitan
        $activeWo = $device->activeWorkOrder;
        if ($activeWo) {
            if ($status === 'TERINSTAL') {
                $activeWo->status = 'terinstal';
                $activeWo->completion_time = now();
                $activeWo->status_notes = $catatan ?: 'Perangkat terinstal di lokasi.';
                $activeWo->save();
            } elseif ($status === 'PENDING') {
                $activeWo->status = 'pending';
                $activeWo->status_notes = $catatan ?: 'Pending di lokasi.';
                $activeWo->save();
            } elseif ($status === 'SAMPAI LOKASI') {
                $activeWo->status = 'sampai_lokasi';
                $activeWo->arrival_time = now();
                $activeWo->save();
            }

            if ($photoPath) {
                WorkOrderEvidence::create([
                    'work_order_id' => $activeWo->id,
                    'technician_id' => auth()->id(),
                    'photo_type' => ($status === 'TERINSTAL') ? 'hasil_instalasi' : 'kendala',
                    'photo_path' => $photoPath,
                    'caption' => 'Update Status Lapangan: ' . $status . ' - ' . $device->registration_number,
                ]);
            }
        }

        // Simpan Catatan Audit Trail (DeviceHistory) lengkap dengan Foto Bukti!
        $noteFull = 'Status: ' . $status . ' | Lokasi: ' . $lokasiAktual;
        if (!empty($nojar)) {
            $noteFull .= ' | NOJAR: ' . $nojar;
        }
        if (!empty($catatan)) {
            $noteFull .= ' | ' . $catatan;
        }

        DeviceHistory::create([
            'device_id' => $device->id,
            'work_order_id' => $activeWo?->id,
            'technician_id' => auth()->id(),
            'actor_id' => auth()->id(),
            'action' => $actionLabel,
            'previous_status' => $oldStatus,
            'new_status' => $device->stock_status,
            'from_location' => $oldLocation,
            'to_location' => $device->current_location,
            'notes' => $noteFull,
            'photo_path' => $photoPath,
        ]);

        return back()->with('success', "Update status lapangan untuk {$device->registration_number} ({$device->name}) berhasil disimpan.");
    }

    public function history(Request $request)
    {
        $technician = auth()->user();
        $query = DeviceHistory::with(['device', 'actor', 'technician', 'workOrder'])
            ->where(function($q) use ($technician) {
                $q->where('technician_id', $technician->id)
                  ->orWhere('actor_id', $technician->id);
            })
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhere('to_location', 'like', "%{$search}%")
                  ->orWhereHas('device', function($dq) use ($search) {
                      $dq->where('registration_number', 'like', "%{$search}%")
                         ->orWhere('name', 'like', "%{$search}%")
                         ->orWhere('nojar', 'like', "%{$search}%");
                  });
            });
        }

        $histories = $query->paginate(20)->withQueryString();

        return view('technician.history.index', compact('histories'));
    }

    protected function authorizeTask(WorkOrder $task)
    {
        if ((int)$task->technician_id !== (int)auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke penugasan ini.');
        }
    }
}
