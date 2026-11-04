<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\DeviceHistory;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_devices' => Device::count(),
            'devices_at_hb_jkt' => Device::whereIn('stock_status', ['tersedia_hb_jkt', 'tersedia_pt', 'tersedia_hb_dikta'])->count(),
            'devices_at_hb_ckr' => Device::where('stock_status', 'tersedia_hb_ckr')->count(),
            'devices_at_hb_dikta' => Device::whereIn('stock_status', ['tersedia_hb_jkt', 'tersedia_pt', 'tersedia_hb_dikta'])->count(),
            'devices_at_pt' => Device::whereIn('stock_status', ['tersedia_hb_jkt', 'tersedia_pt', 'tersedia_hb_dikta'])->count(),
            'devices_with_tech' => Device::where('stock_status', 'dibawa_teknisi')->count(),
            'devices_installed' => Device::whereIn('stock_status', ['terpasang_pelanggan', 'terinstal'])->count(),
            'devices_pending' => Device::where('stock_status', 'pending')->count(),
            'devices_broken' => Device::where('stock_status', 'rusak')->count(),
            'devices_lost' => Device::where('stock_status', 'hilang')->count(),
            'devices_dismantle' => Device::where('stock_status', 'dismantle')->count(),
            'total_technicians' => User::where('role', 'teknisi')->count(),
            'active_technicians' => User::where('role', 'teknisi')->where('status', 'active')->count(),
            'total_work_orders' => WorkOrder::count(),
            'active_work_orders' => WorkOrder::whereNotIn('status', ['selesai', 'terinstal'])->count(),
        ];

        $statusFilter = request('status', 'dibawa_teknisi');
        $search = request('search');

        $query = Device::with(['currentTechnician', 'activeWorkOrder.technician', 'histories' => function ($q) {
            $q->with(['technician', 'actor'])->latest()->take(1);
        }]);

        switch ($statusFilter) {
            case 'tersedia_hb_jkt':
            case 'tersedia_hb_dikta':
                $query->whereIn('stock_status', ['tersedia_hb_jkt', 'tersedia_pt', 'tersedia_hb_dikta']);
                break;
            case 'tersedia_hb_ckr':
                $query->where('stock_status', 'tersedia_hb_ckr');
                break;
            case 'dibawa_teknisi':
                $query->where('stock_status', 'dibawa_teknisi');
                break;
            case 'terinstal':
                $query->whereIn('stock_status', ['terpasang_pelanggan', 'terinstal']);
                break;
            case 'pending':
                $query->where('stock_status', 'pending');
                break;
            case 'rusak':
                $query->where('stock_status', 'rusak');
                break;
            case 'hilang':
                $query->where('stock_status', 'hilang');
                break;
            case 'all':
            default:
                // Tampilkan semua perangkat
                break;
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('registration_number', 'like', "%{$search}%")
                  ->orWhere('kode_spek', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('nojar', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhereHas('activeWorkOrder', function ($woQ) use ($search) {
                      $woQ->where('nojar', 'like', "%{$search}%")
                          ->orWhere('customer_name', 'like', "%{$search}%");
                  });
            });
        }

        $devices = $query->latest('updated_at')->get();

        $recentHistories = DeviceHistory::with(['device', 'technician', 'actor', 'workOrder'])
            ->latest()
            ->take(8)
            ->get();

        $devicesInField = Device::with(['currentTechnician', 'activeWorkOrder'])
            ->where('stock_status', 'dibawa_teknisi')
            ->latest()
            ->get();

        $stockRecap = Device::selectRaw('type, count(*) as total, 
            sum(case when stock_status in ("tersedia_hb_jkt", "tersedia_pt", "tersedia_hb_dikta") then 1 else 0 end) as tersedia_jkt, 
            sum(case when stock_status = "tersedia_hb_ckr" then 1 else 0 end) as tersedia_ckr, 
            sum(case when stock_status in ("tersedia_hb_jkt", "tersedia_hb_ckr", "tersedia_pt", "tersedia_hb_dikta") then 1 else 0 end) as tersedia, 
            sum(case when stock_status = "dibawa_teknisi" then 1 else 0 end) as di_lapangan,
            sum(case when stock_status in ("terpasang_pelanggan", "terinstal") then 1 else 0 end) as terinstal,
            sum(case when stock_status = "pending" then 1 else 0 end) as pending,
            sum(case when stock_status = "rusak" then 1 else 0 end) as rusak,
            sum(case when stock_status = "hilang" then 1 else 0 end) as hilang')
            ->groupBy('type')
            ->get();

        $technicians = User::where('role', 'teknisi')->orderBy('name')->get();

        return view('admin.dashboard', compact('stats', 'recentHistories', 'devicesInField', 'devices', 'stockRecap', 'statusFilter', 'technicians'));
    }
}
