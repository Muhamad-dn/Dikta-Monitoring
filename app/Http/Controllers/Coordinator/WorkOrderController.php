<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\Device;
use App\Models\User;
use App\Models\DeviceHistory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WorkOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = WorkOrder::with(['technician', 'device', 'coordinator'])->latest();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('technician_id')) {
            $query->where('technician_id', $request->technician_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nojar', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('location_address', 'like', "%{$search}%")
                  ->orWhereHas('device', function ($dq) use ($search) {
                      $dq->where('registration_number', 'like', "%{$search}%")
                         ->orWhere('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('technician', function ($tq) use ($search) {
                      $tq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $workOrders = $query->paginate(10)->withQueryString();
        $technicians = User::where('role', 'teknisi')->where('status', 'active')->orderBy('name')->get();

        return view('coordinator.work_orders.index', compact('workOrders', 'technicians'));
    }

    public function create()
    {
        $technicians = User::where('role', 'teknisi')->where('status', 'active')->orderBy('name')->get();
        $availableDevices = Device::where('stock_status', 'tersedia_pt')->orderBy('registration_number')->get();

        $suggestedNojar = '';

        return view('coordinator.work_orders.create', compact('technicians', 'availableDevices', 'suggestedNojar'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nojar' => 'required|string|max:50|unique:work_orders,nojar',
            'title' => 'required|string|max:150',
            'category' => 'required|in:Survei,Instalasi,Aktivasi,Troubleshooting,Dismantle',
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'nullable|string|max:25',
            'location_address' => 'required|string',
            'technician_id' => 'required|exists:users,id',
            'device_id' => 'nullable|exists:devices,id',
            'status_notes' => 'nullable|string',
        ]);

        $validated['coordinator_id'] = auth()->id();
        $validated['status'] = 'belum_dikerjakan';

        $workOrder = WorkOrder::create($validated);

        // Jika ada perangkat yang dialokasikan, catat riwayat bahwa perangkat ditugaskan untuk NOJAR ini
        if ($workOrder->device_id) {
            $device = Device::find($workOrder->device_id);
            $technician = User::find($workOrder->technician_id);

            DeviceHistory::create([
                'device_id' => $device->id,
                'work_order_id' => $workOrder->id,
                'technician_id' => $technician->id,
                'actor_id' => auth()->id(),
                'action' => 'Penugasan oleh Koordinator',
                'previous_status' => $device->stock_status,
                'new_status' => $device->stock_status,
                'from_location' => $device->current_location,
                'to_location' => 'Gudang PT DIKTA (Alokasi: ' . $technician->name . ')',
                'notes' => "Dialokasikan untuk NOJAR {$workOrder->nojar} ke teknisi {$technician->name}. Menunggu pengambilan di gudang PT.",
            ]);
        }

        return redirect()->route('coordinator.work-orders.show', $workOrder)->with('success', "Penugasan pekerjaan NOJAR {$workOrder->nojar} berhasil dibuat.");
    }

    public function show(WorkOrder $workOrder)
    {
        $workOrder->load([
            'coordinator',
            'technician',
            'device.currentTechnician',
            'evidence.technician',
            'histories.actor',
            'histories.technician',
        ]);

        return view('coordinator.work_orders.show', compact('workOrder'));
    }

    public function edit(WorkOrder $workOrder)
    {
        $technicians = User::where('role', 'teknisi')->where('status', 'active')->orderBy('name')->get();
        $availableDevices = Device::where(function ($q) use ($workOrder) {
            $q->where('stock_status', 'tersedia_pt')
              ->orWhere('id', $workOrder->device_id);
        })->orderBy('registration_number')->get();

        return view('coordinator.work_orders.edit', compact('workOrder', 'technicians', 'availableDevices'));
    }

    public function update(Request $request, WorkOrder $workOrder)
    {
        $validated = $request->validate([
            'nojar' => ['required', 'string', 'max:50', Rule::unique('work_orders')->ignore($workOrder->id)],
            'title' => 'required|string|max:150',
            'category' => 'required|in:Survei,Instalasi,Aktivasi,Troubleshooting,Dismantle',
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'nullable|string|max:25',
            'location_address' => 'required|string',
            'technician_id' => 'required|exists:users,id',
            'device_id' => 'nullable|exists:devices,id',
            'status_notes' => 'nullable|string',
        ]);

        $oldDeviceId = $workOrder->device_id;
        $workOrder->update($validated);

        // Jika perangkat diganti
        if ($oldDeviceId !== $workOrder->device_id && $workOrder->device_id) {
            $newDevice = Device::find($workOrder->device_id);
            $technician = User::find($workOrder->technician_id);

            DeviceHistory::create([
                'device_id' => $newDevice->id,
                'work_order_id' => $workOrder->id,
                'technician_id' => $technician->id,
                'actor_id' => auth()->id(),
                'action' => 'Perubahan Alokasi Perangkat oleh Koordinator',
                'previous_status' => $newDevice->stock_status,
                'new_status' => $newDevice->stock_status,
                'from_location' => $newDevice->current_location,
                'to_location' => 'Gudang PT DIKTA (Alokasi Baru: ' . $technician->name . ')',
                'notes' => "Dialihkan ke pekerjaan NOJAR {$workOrder->nojar}.",
            ]);
        }

        return redirect()->route('coordinator.work-orders.show', $workOrder)->with('success', 'Data penugasan berhasil diperbarui.');
    }

    public function destroy(WorkOrder $workOrder)
    {
        if (in_array($workOrder->status, ['perangkat_diambil', 'dalam_perjalanan', 'sampai_lokasi', 'sedang_dikerjakan'])) {
            return back()->with('error', 'Pekerjaan tidak dapat dihapus karena teknisi sedang memproses perangkat di lapangan!');
        }

        $nojar = $workOrder->nojar;

        // If device was linked and not yet completed/dismantled, restore to PT stock
        if ($workOrder->device && in_array($workOrder->device->stock_status, ['tersedia_pt', 'dibawa_teknisi'])) {
            $workOrder->device->update([
                'stock_status' => 'tersedia_pt',
                'current_technician_id' => null,
                'current_location' => 'Gudang PT DIKTA (Alokasi Batal)',
            ]);
        }

        $workOrder->delete();

        return redirect()->route('coordinator.work-orders.index')->with('success', "Penugasan NOJAR {$nojar} berhasil dibatalkan/dihapus.");
    }
}
