<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeviceHistory;
use App\Models\Device;
use App\Models\User;
use Illuminate\Http\Request;

class DeviceHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = DeviceHistory::with(['device', 'technician', 'actor', 'workOrder'])->latest();

        if ($request->filled('device_id')) {
            $query->where('device_id', $request->device_id);
        }

        if ($request->filled('technician_id')) {
            $query->where('technician_id', $request->technician_id);
        }

        if ($request->filled('search')) {
            $searchLower = strtolower(trim($request->search));
            $query->where(function ($q) use ($searchLower) {
                $q->whereRaw('LOWER(action) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereRaw('LOWER(from_location) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereRaw('LOWER(to_location) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereRaw('LOWER(notes) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereHas('device', function ($dq) use ($searchLower) {
                      $dq->whereRaw('LOWER(registration_number) LIKE ?', ["%{$searchLower}%"])
                         ->orWhereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                         ->orWhereRaw('LOWER(nojar) LIKE ?', ["%{$searchLower}%"])
                         ->orWhereRaw('LOWER(area) LIKE ?', ["%{$searchLower}%"]);
                  });
            });
        }

        $histories = $query->paginate(15)->withQueryString();
        $devices = Device::orderBy('registration_number')->get(['id', 'registration_number', 'name']);
        $technicians = User::where('role', 'teknisi')->orderBy('name')->get(['id', 'name']);

        return view('admin.history.index', compact('histories', 'devices', 'technicians'));
    }
}
