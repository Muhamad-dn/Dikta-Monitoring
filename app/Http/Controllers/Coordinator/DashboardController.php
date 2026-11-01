<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\Device;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_jobs' => WorkOrder::count(),
            'belum_dikerjakan' => WorkOrder::where('status', 'belum_dikerjakan')->count(),
            'diambil_teknisi' => WorkOrder::where('status', 'perangkat_diambil')->count(),
            'dalam_perjalanan' => WorkOrder::where('status', 'dalam_perjalanan')->count(),
            'sampai_lokasi' => WorkOrder::where('status', 'sampai_lokasi')->count(),
            'sedang_dikerjakan' => WorkOrder::where('status', 'sedang_dikerjakan')->count(),
            'selesai' => WorkOrder::whereIn('status', ['selesai', 'terinstal'])->count(),
            'pending' => WorkOrder::where('status', 'pending')->count(),
            'gagal' => WorkOrder::where('status', 'gagal_install')->count(),
        ];

        $recentJobs = WorkOrder::with(['technician', 'device', 'coordinator'])
            ->latest()
            ->take(8)
            ->get();

        $activeFieldDevices = Device::with(['currentTechnician', 'activeWorkOrder'])
            ->where('stock_status', 'dibawa_teknisi')
            ->latest()
            ->get();

        $techniciansStatus = User::where('role', 'teknisi')
            ->withCount(['carriedDevices', 'workOrdersAsTechnician as active_jobs' => function ($q) {
                $q->whereNotIn('status', ['selesai', 'terinstal']);
            }])
            ->get();

        return view('coordinator.dashboard', compact('stats', 'recentJobs', 'activeFieldDevices', 'techniciansStatus'));
    }
}
