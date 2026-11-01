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
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('from_location', 'like', "%{$search}%")
                  ->orWhere('to_location', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('device', function ($dq) use ($search) {
                      $dq->where('registration_number', 'like', "%{$search}%")
                         ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        $histories = $query->paginate(15)->withQueryString();
        $devices = Device::orderBy('registration_number')->get(['id', 'registration_number', 'name']);
        $technicians = User::where('role', 'teknisi')->orderBy('name')->get(['id', 'name']);

        return view('admin.history.index', compact('histories', 'devices', 'technicians'));
    }
}
