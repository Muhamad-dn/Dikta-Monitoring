<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\Device;
use App\Models\DeviceHistory;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $technician = auth()->user();

        $activeTasks = WorkOrder::with(['device', 'coordinator'])
            ->where('technician_id', $technician->id)
            ->whereNotIn('status', ['selesai', 'terinstal'])
            ->orderByRaw("CASE 
                WHEN status = 'sedang_dikerjakan' THEN 1
                WHEN status = 'sampai_lokasi' THEN 2
                WHEN status = 'dalam_perjalanan' THEN 3
                WHEN status = 'perangkat_diambil' THEN 4
                WHEN status = 'belum_dikerjakan' THEN 5
                ELSE 6 END")
            ->get();

        $completedTasks = WorkOrder::with(['device', 'coordinator'])
            ->where('technician_id', $technician->id)
            ->whereIn('status', ['selesai', 'terinstal'])
            ->latest('completion_time')
            ->take(5)
            ->get();

        $carriedDevices = Device::with(['activeWorkOrder', 'histories'])
            ->where('current_technician_id', $technician->id)
            ->whereIn('stock_status', ['dibawa_teknisi', 'pending'])
            ->latest('updated_at')
            ->get();

        $otherTechnicians = User::where('role', 'teknisi')
            ->where('id', '!=', $technician->id)
            ->orderBy('name')
            ->get();

        $allTechnicians = User::where('role', 'teknisi')
            ->orderBy('name')
            ->get();

        $recentHistories = DeviceHistory::with(['device', 'actor', 'technician', 'workOrder'])
            ->where(function($q) use ($technician) {
                $q->where('technician_id', $technician->id)
                  ->orWhere('actor_id', $technician->id);
            })
            ->latest()
            ->take(15)
            ->get();

        $stats = [
            'active_count' => $activeTasks->count(),
            'needs_pickup' => $activeTasks->where('status', 'belum_dikerjakan')->count(),
            'in_progress' => $activeTasks->whereIn('status', ['dalam_perjalanan', 'sampai_lokasi', 'sedang_dikerjakan'])->count(),
            'completed_count' => WorkOrder::where('technician_id', $technician->id)->whereIn('status', ['selesai', 'terinstal'])->count(),
            'carried_devices_count' => $carriedDevices->count(),
        ];

        // Riwayat instalasi hari ini untuk laporan cepat WhatsApp (seperti di screenshot)
        $todayInstalled = DeviceHistory::with(['device', 'workOrder'])
            ->where(function($q) use ($technician) {
                $q->where('technician_id', $technician->id)
                  ->orWhere('actor_id', $technician->id);
            })
            ->whereDate('created_at', today())
            ->where(function($q) {
                $q->where('new_status', 'like', '%terinstal%')
                  ->orWhere('action', 'like', '%terinstal%');
            })
            ->latest()
            ->get();

        $reportDate = \Carbon\Carbon::now()->isoFormat('D MMMM YYYY');
        $reportLines = ["Laporan Instalasi Hari Ini - " . $reportDate, ""];
        if ($todayInstalled->count() > 0) {
            foreach ($todayInstalled as $idx => $item) {
                $num = $idx + 1;
                $nojar = $item->workOrder?->nojar ?: ($item->device?->nojar ?: '-');
                $deviceName = $item->device?->name ?: 'Perangkat';
                $reg = $item->device?->registration_number ?: '-';
                $customer = $item->workOrder?->customer_name ?: ($item->device?->customer_name ?: '-');
                $address = $item->to_location ?: ($item->device?->address ?: ($item->workOrder?->location_address ?: '-'));
                $reportLines[] = "{$num}. NOJAR: {$nojar}";
                $reportLines[] = "   Perangkat: {$deviceName} ({$reg})";
                $reportLines[] = "   Pelanggan: {$customer}";
                $reportLines[] = "   Alamat: {$address}";
                $reportLines[] = "   Status: Terinstal";
                $reportLines[] = "";
            }
        } else {
            $reportLines[] = "Belum ada perangkat yang diinstal hari ini.";
        }
        $todayReportText = implode("\n", $reportLines);

        $areas = ['BWA', 'BDA', 'SEOA', 'SAST', 'CDA', 'NEA'];
        $activities = ['Instalasi Baru', 'Ganti Perangkat / Rusak', 'Upgrade Perangkat', 'Troubleshoot', 'Simulasi', 'Spare Dikta'];

        return view('technician.dashboard', compact(
            'activeTasks', 
            'completedTasks', 
            'carriedDevices', 
            'otherTechnicians', 
            'allTechnicians',
            'recentHistories', 
            'todayInstalled',
            'todayReportText',
            'stats',
            'areas',
            'activities'
        ));
    }
}
