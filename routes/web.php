<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DeviceController as AdminDeviceController;
use App\Http\Controllers\Admin\TechnicianController as AdminTechnicianController;
use App\Http\Controllers\Admin\DeviceHistoryController as AdminDeviceHistoryController;
use App\Http\Controllers\Coordinator\DashboardController as CoordDashboardController;

use App\Http\Controllers\Coordinator\WorkOrderController as CoordWorkOrderController;
use App\Http\Controllers\Coordinator\DeviceTrackingController as CoordDeviceTrackingController;
use App\Http\Controllers\Technician\DashboardController as TechDashboardController;
use App\Http\Controllers\Technician\TaskController as TechTaskController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Root redirect
Route::get('/', function () {
    if (auth()->check()) {
        return match (auth()->user()->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'koordinator' => redirect()->route('coordinator.dashboard'),
            'teknisi' => redirect()->route('technician.dashboard'),
            default => redirect()->route('login'),
        };
    }
    return redirect()->route('login');
});

// Authentication
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Admin Routes (Role: admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Devices & Stock
    Route::get('/devices/returns', [AdminDeviceController::class, 'returnIndex'])->name('devices.returns');
    Route::post('/devices/process-return', [AdminDeviceController::class, 'processReturn'])->name('devices.process-return');
    Route::get('/devices/stock', [AdminDeviceController::class, 'stock'])->name('devices.stock');
    Route::get('/devices/check-registration', [AdminDeviceController::class, 'checkRegistration'])->name('devices.check-registration');
    Route::get('/devices/check-spec', [AdminDeviceController::class, 'checkSpec'])->name('devices.check-spec');
    Route::get('/devices/{device}/print-label', [AdminDeviceController::class, 'printLabel'])->name('devices.print-label');
    Route::get('/devices/{device}/evidence', [AdminDeviceController::class, 'evidence'])->name('devices.evidence');
    Route::post('/devices/{device}/allocate', [AdminDeviceController::class, 'allocateToTechnician'])->name('devices.allocate');
    Route::post('/devices/{device}/return-lintasarta', [AdminDeviceController::class, 'returnToLintasarta'])->name('devices.return-lintasarta');
    Route::patch('/devices/{device}/lintasarta', [AdminDeviceController::class, 'updateLintasarta'])->name('devices.update-lintasarta');
    Route::patch('/devices/{device}/area', [AdminDeviceController::class, 'updateArea'])->name('devices.update-area');
    Route::get('/devices/import-template', [AdminDeviceController::class, 'downloadTemplate'])->name('devices.import-template');
    Route::post('/devices/import', [AdminDeviceController::class, 'importExcel'])->name('devices.import');
    Route::resource('/devices', AdminDeviceController::class);

    // Technicians
    Route::patch('/technicians/{technician}/toggle-status', [AdminTechnicianController::class, 'toggleStatus'])->name('technicians.toggle-status');
    Route::resource('/technicians', AdminTechnicianController::class)->except(['show']);

    // Audit Trail / History
    Route::get('/history', [AdminDeviceHistoryController::class, 'index'])->name('history.index');

});

/*
|--------------------------------------------------------------------------
| Coordinator Routes (Role: koordinator)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:koordinator'])->prefix('coordinator')->name('coordinator.')->group(function () {
    Route::get('/dashboard', [CoordDashboardController::class, 'index'])->name('dashboard');

    // Work Orders
    Route::resource('/work-orders', CoordWorkOrderController::class);

    // Device Tracking Matrix
    Route::get('/tracking', [CoordDeviceTrackingController::class, 'index'])->name('tracking.index');
    Route::get('/tracking/{device}', [CoordDeviceTrackingController::class, 'detail'])->name('tracking.detail');
});

/*
|--------------------------------------------------------------------------
| Technician Routes (Role: teknisi)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:teknisi'])->prefix('technician')->name('technician.')->group(function () {
    Route::get('/dashboard', [TechDashboardController::class, 'index'])->name('dashboard');

    // Tasks & Field Operations
    Route::get('/tasks/{task}', [TechTaskController::class, 'show'])->name('tasks.show');
    Route::get('/tasks/{task}/scan-pickup', [TechTaskController::class, 'scanPickup'])->name('tasks.scan-pickup');
    Route::post('/tasks/{task}/confirm-pickup', [TechTaskController::class, 'confirmPickup'])->name('tasks.confirm-pickup');
    Route::post('/tasks/{task}/flow', [TechTaskController::class, 'updateFlow'])->name('tasks.flow');
    Route::get('/tasks/{task}/complete', [TechTaskController::class, 'showComplete'])->name('tasks.complete');
    Route::post('/tasks/{task}/submit-complete', [TechTaskController::class, 'submitComplete'])->name('tasks.submit-complete');

    // Carried Devices
    Route::get('/my-devices', [TechTaskController::class, 'myDevices'])->name('devices.index');
    Route::post('/devices/{device}/handover', [TechTaskController::class, 'handoverDevice'])->name('devices.handover');
    Route::post('/devices/{device}/return', [TechTaskController::class, 'returnDevice'])->name('devices.return');
    Route::post('/devices/{device}/report-broken', [TechTaskController::class, 'reportBroken'])->name('devices.report-broken');
    Route::post('/devices/{device}/report-lost', [TechTaskController::class, 'reportLost'])->name('devices.report-lost');

    // Device Registration by Technician
    Route::get('/devices/create', [TechTaskController::class, 'createDevice'])->name('devices.create');
    Route::post('/devices/store', [TechTaskController::class, 'storeDevice'])->name('devices.store');
    Route::get('/devices/check-registration', [TechTaskController::class, 'checkRegistration'])->name('devices.check-registration');
    Route::get('/devices/check-spec', [TechTaskController::class, 'checkSpec'])->name('devices.check-spec');

    // Field Status Update
    Route::post('/devices/update-status', [TechTaskController::class, 'updateFieldStatus'])->name('devices.update-status');

    // History
    Route::get('/history', [TechTaskController::class, 'history'])->name('history.index');
});
