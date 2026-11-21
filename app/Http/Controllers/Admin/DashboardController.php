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
        $deviceStats = Device::selectRaw("
            count(*) as total_devices,
            sum(case when stock_status in ('tersedia_hb_jkt', 'tersedia_pt', 'tersedia_hb_dikta') then 1 else 0 end) as devices_at_hb_jkt,
            sum(case when stock_status = 'tersedia_hb_ckr' then 1 else 0 end) as devices_at_hb_ckr,
            sum(case when stock_status in ('tersedia_hb_jkt', 'tersedia_pt', 'tersedia_hb_dikta') then 1 else 0 end) as devices_at_hb_dikta,
            sum(case when stock_status in ('tersedia_hb_jkt', 'tersedia_pt', 'tersedia_hb_dikta') then 1 else 0 end) as devices_at_pt,
            sum(case when stock_status = 'dibawa_teknisi' then 1 else 0 end) as devices_with_tech,
            sum(case when stock_status in ('terpasang_pelanggan', 'terinstal') then 1 else 0 end) as devices_installed,
            sum(case when stock_status = 'pending' then 1 else 0 end) as devices_pending,
            sum(case when stock_status = 'rusak' then 1 else 0 end) as devices_broken,
            sum(case when stock_status = 'hilang' then 1 else 0 end) as devices_lost,
            sum(case when stock_status = 'dismantle' then 1 else 0 end) as devices_dismantle
        ")->first();

        $techStats = User::where('role', 'teknisi')->selectRaw("
            count(*) as total_technicians,
            sum(case when status = 'active' then 1 else 0 end) as active_technicians
        ")->first();

        $woStats = WorkOrder::selectRaw("
            count(*) as total_work_orders,
            sum(case when status not in ('selesai', 'terinstal') then 1 else 0 end) as active_work_orders
        ")->first();

        $stats = [
            'total_devices' => (int) ($deviceStats->total_devices ?? 0),
            'devices_at_hb_jkt' => (int) ($deviceStats->devices_at_hb_jkt ?? 0),
            'devices_at_hb_ckr' => (int) ($deviceStats->devices_at_hb_ckr ?? 0),
            'devices_at_hb_dikta' => (int) ($deviceStats->devices_at_hb_dikta ?? 0),
            'devices_at_pt' => (int) ($deviceStats->devices_at_pt ?? 0),
            'devices_with_tech' => (int) ($deviceStats->devices_with_tech ?? 0),
            'devices_installed' => (int) ($deviceStats->devices_installed ?? 0),
            'devices_pending' => (int) ($deviceStats->devices_pending ?? 0),
            'devices_broken' => (int) ($deviceStats->devices_broken ?? 0),
            'devices_lost' => (int) ($deviceStats->devices_lost ?? 0),
            'devices_dismantle' => (int) ($deviceStats->devices_dismantle ?? 0),
            'total_technicians' => (int) ($techStats->total_technicians ?? 0),
            'active_technicians' => (int) ($techStats->active_technicians ?? 0),
            'total_work_orders' => (int) ($woStats->total_work_orders ?? 0),
            'active_work_orders' => (int) ($woStats->active_work_orders ?? 0),
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
            $searchLower = strtolower(trim($search));
            $query->where(function ($q) use ($searchLower) {
                $q->whereRaw('LOWER(registration_number) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereRaw('LOWER(kode_spek) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereRaw('LOWER(serial_number) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereRaw('LOWER(nojar) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereRaw('LOWER(area) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereRaw('LOWER(customer_name) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereHas('activeWorkOrder', function ($woQ) use ($searchLower) {
                      $woQ->whereRaw('LOWER(nojar) LIKE ?', ["%{$searchLower}%"])
                          ->orWhereRaw('LOWER(customer_name) LIKE ?', ["%{$searchLower}%"]);
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

        $stockRecap = Device::selectRaw("type, count(*) as total, 
            sum(case when stock_status in ('tersedia_hb_jkt', 'tersedia_pt', 'tersedia_hb_dikta') then 1 else 0 end) as tersedia_jkt, 
            sum(case when stock_status = 'tersedia_hb_ckr' then 1 else 0 end) as tersedia_ckr, 
            sum(case when stock_status in ('tersedia_hb_jkt', 'tersedia_hb_ckr', 'tersedia_pt', 'tersedia_hb_dikta') then 1 else 0 end) as tersedia, 
            sum(case when stock_status = 'dibawa_teknisi' then 1 else 0 end) as di_lapangan,
            sum(case when stock_status in ('terpasang_pelanggan', 'terinstal') then 1 else 0 end) as terinstal,
            sum(case when stock_status = 'pending' then 1 else 0 end) as pending,
            sum(case when stock_status = 'rusak' then 1 else 0 end) as rusak,
            sum(case when stock_status = 'hilang' then 1 else 0 end) as hilang")
            ->groupBy('type')
            ->get();

        $technicians = User::where('role', 'teknisi')->orderBy('name')->get();

        return view('admin.dashboard', compact('stats', 'recentHistories', 'devicesInField', 'devices', 'stockRecap', 'statusFilter', 'technicians'));
    }
}
