<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\User;
use Illuminate\Http\Request;

class DeviceTrackingController extends Controller
{
    public function index(Request $request)
    {
        $query = Device::with(['currentTechnician', 'activeWorkOrder', 'histories' => function ($q) {
            $q->latest()->take(1);
        }])->latest('updated_at');

        // Filter: teknisi pembawa
        if ($request->filled('technician_id')) {
            $query->where('current_technician_id', $request->technician_id);
        }

        // Filter: status perangkat
        if ($request->filled('stock_status')) {
            $query->where('stock_status', $request->stock_status);
        }

        // Pencarian multi-kriteria: nomor registrasi, serial number, nama, nojar, area, atau lokasi
        if ($request->filled('search')) {
            $searchLower = strtolower(trim($request->search));
            $query->where(function ($q) use ($searchLower) {
                $q->whereRaw('LOWER(registration_number) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereRaw('LOWER(serial_number) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereRaw('LOWER(nojar) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereRaw('LOWER(area) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereRaw('LOWER(current_location) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereHas('currentTechnician', function ($tq) use ($searchLower) {
                      $tq->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"]);
                  });
            });
        }

        $devices = $query->paginate(12)->withQueryString();
        $technicians = User::where('role', 'teknisi')->orderBy('name')->get();

        $stats = [
            'total_field' => Device::where('stock_status', 'dibawa_teknisi')->count(),
            'total_installed' => Device::where('stock_status', 'terpasang_pelanggan')->count(),
            'total_warehouse' => Device::where('stock_status', 'tersedia_pt')->count(),
            'total_dismantle' => Device::where('stock_status', 'dismantle')->count(),
        ];

        return view('coordinator.tracking.index', compact('devices', 'technicians', 'stats'));
    }

    public function detail(Device $device)
    {
        $device->load([
            'currentTechnician',
            'activeWorkOrder',
            'histories.actor',
            'histories.technician',
            'histories.workOrder'
        ]);

        return view('coordinator.tracking.detail', compact('device'));
    }
}
