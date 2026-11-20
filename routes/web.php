<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DeviceController as AdminDeviceController;
use App\Http\Controllers\Admin\TechnicianController as AdminTechnicianController;
use App\Http\Controllers\Admin\DeviceHistoryController as AdminDeviceHistoryController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\StockOpnameController as AdminStockOpnameController;
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
    Route::get('/devices/search-suggestions', [AdminDeviceController::class, 'searchSuggestions'])->name('devices.search-suggestions');
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

    // Stock Opname
    Route::match(['get', 'post'], '/stock-opname/start', [AdminStockOpnameController::class, 'start'])->name('stock-opname.start');
    Route::get('/stock-opname', [AdminStockOpnameController::class, 'index'])->name('stock-opname.index');
    Route::post('/stock-opname', [AdminStockOpnameController::class, 'store'])->name('stock-opname.store');
    Route::get('/stock-opname/{stockOpname}', [AdminStockOpnameController::class, 'show'])->name('stock-opname.show');
    Route::post('/stock-opname/{stockOpname}/scan', [AdminStockOpnameController::class, 'scan'])->name('stock-opname.scan');
    Route::post('/stock-opname/{stockOpname}/complete', [AdminStockOpnameController::class, 'complete'])->name('stock-opname.complete');
    Route::post('/stock-opname/{stockOpname}/items/{item}/resolve', [AdminStockOpnameController::class, 'resolveDiscrepancy'])->name('stock-opname.resolve');
    Route::get('/stock-opname/{stockOpname}/export', [AdminStockOpnameController::class, 'export'])->name('stock-opname.export');
    Route::get('/stock-opname/{stockOpname}/print', [AdminStockOpnameController::class, 'print'])->name('stock-opname.print');
    Route::delete('/stock-opname/{stockOpname}', [AdminStockOpnameController::class, 'destroy'])->name('stock-opname.destroy');

    // Technicians
    Route::patch('/technicians/{technician}/toggle-status', [AdminTechnicianController::class, 'toggleStatus'])->name('technicians.toggle-status');
    Route::resource('/technicians', AdminTechnicianController::class)->except(['show']);

    // Audit Trail / History
    Route::get('/history', [AdminDeviceHistoryController::class, 'index'])->name('history.index');

    // Admin Notifications
    Route::get('/notifications/{notification}/go', [AdminNotificationController::class, 'go'])->name('notifications.go');
    Route::post('/notifications/{notification}/read', [AdminNotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [AdminNotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::get('/notifications/poll', [AdminNotificationController::class, 'poll'])->name('notifications.poll');

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

// Route inisialisasi / migrasi database cloud (Supabase / Postgres)
Route::get('/setup-supabase', function (\Illuminate\Http\Request $request) {
    if ($request->query('key') !== 'dikta2026') {
        abort(403, 'Akses ditolak: Kunci rahasia tidak valid.');
    }

    try {
        $connection = config('database.default');
        $dbName = config("database.connections.{$connection}.database");
        $dbHost = config("database.connections.{$connection}.host");

        // Diagnostik variabel environment
        $envDiag = [
            'getenv_DB_CONNECTION' => getenv('DB_CONNECTION') ?: '(kosong)',
            'ENV_DB_CONNECTION' => $_ENV['DB_CONNECTION'] ?? '(kosong)',
            'SERVER_DB_CONNECTION' => $_SERVER['DB_CONNECTION'] ?? '(kosong)',
            'getenv_DB_HOST' => getenv('DB_HOST') ?: '(kosong)',
            'ENV_DB_HOST' => $_ENV['DB_HOST'] ?? '(kosong)',
            'SERVER_DB_HOST' => $_SERVER['DB_HOST'] ?? '(kosong)',
            'config_default' => $connection,
        ];

        // 1. Jalankan Migrasi Tabel
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);

        // 2. Jalankan Seeder jika user masih kosong
        $seeded = false;
        if (\App\Models\User::count() === 0) {
            \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
            $seeded = true;
        }

        $userCount = \App\Models\User::count();
        $deviceCount = \App\Models\Device::count();

        return response("
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>Inisialisasi Database Berhasil</title>
            <style>
                body { font-family: sans-serif; background: #0f172a; color: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
                .card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 32px; max-width: 600px; width: 100%; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
                h1 { color: #10b981; font-size: 22px; margin-top: 0; display: flex; align-items: center; gap: 8px; }
                .info { background: #0f172a; padding: 16px; border-radius: 8px; font-family: monospace; font-size: 13px; line-height: 1.6; margin: 16px 0; color: #94a3b8; }
                .info strong { color: #f1f5f9; }
                .btn { display: inline-block; background: #2563eb; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold; margin-top: 10px; }
            </style>
        </head>
        <body>
            <div class='card'>
                <h1><span>&#10004;</span> Database Berhasil Diinisialisasi!</h1>
                <p>Database cloud Anda telah terhubung dan siap digunakan secara permanen.</p>
                <div class='info'>
                    <div>Koneksi Database Aktif: <strong>{$connection}</strong> ({$dbHost})</div>
                    <div>Nama Database: <strong>{$dbName}</strong></div>
                    <div style='margin-top:10px; padding:10px; background:#1e293b; border-radius:6px;'>
                        <div style='color:#38bdf8; font-weight:bold;'>Status Variabel di Vercel:</div>
                        <div>getenv(DB_CONNECTION): <strong>{$envDiag['getenv_DB_CONNECTION']}</strong></div>
                        <div>\$_ENV['DB_CONNECTION']: <strong>{$envDiag['ENV_DB_CONNECTION']}</strong></div>
                        <div>\$_SERVER['DB_CONNECTION']: <strong>{$envDiag['SERVER_DB_CONNECTION']}</strong></div>
                        <div>DB_HOST: <strong>{$envDiag['getenv_DB_HOST']}</strong></div>
                    </div>
                    <div>Total Pengguna Siap: <strong>{$userCount} Akun</strong></div>
                    <div>Total Perangkat: <strong>{$deviceCount} Unit</strong></div>
                    <div>Status Seeder: <strong>" . ($seeded ? 'Data awal berhasil dimuat' : 'Data sudah ada sebelumnya') . "</strong></div>
                </div>
                <a href='/login' class='btn'>Buka Halaman Login &rarr;</a>
            </div>
        </body>
        </html>
        ", 200, ['Content-Type' => 'text/html']);
    } catch (\Throwable $e) {
        return response("
        <!DOCTYPE html>
        <html>
        <head><title>Gagal Inisialisasi Database</title></head>
        <body style='font-family:sans-serif; background:#0f172a; color:#f87171; padding:40px;'>
            <h2>&#10060; Terjadi Kesalahan saat Migrasi:</h2>
            <pre style='background:#1e293b; color:#f1f5f9; padding:16px; border-radius:8px; overflow-x:auto;'>" . htmlspecialchars($e->getMessage()) . "</pre>
            <p style='color:#94a3b8;'>Pastikan data DB_HOST, DB_PASSWORD, dan kredensial Supabase di Vercel Environment Variables sudah benar, lalu coba lagi.</p>
        </body>
        </html>
        ", 500, ['Content-Type' => 'text/html']);
    }
});
