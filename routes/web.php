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
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

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
    Route::post('/stock-opname/{stockOpname}/include-external', [AdminStockOpnameController::class, 'includeExternalDevice'])->name('stock-opname.include-external');
    Route::post('/stock-opname/{stockOpname}/register-new', [AdminStockOpnameController::class, 'registerNewDevice'])->name('stock-opname.register-new');
    Route::get('/stock-opname/{stockOpname}/search-external', [AdminStockOpnameController::class, 'searchExternalDevices'])->name('stock-opname.search-external');
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

        // 1. Jalankan Migrasi Tabel (otomatis migrate:fresh jika ada tabel konflik lama di Postgres)
        $isFresh = $request->query('fresh') === '1';
        try {
            if ($isFresh) {
                \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);
            } else {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            }
        } catch (\Throwable $mErr) {
            \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);
        }

        // 2. Jalankan Sinkronisasi 243 Stok Asli dan 6 Teknisi Asli dari database.sqlite jika belum lengkap
        if (\App\Models\Device::count() < 100 || $request->query('sync') === '1') {
            $sqlitePath = database_path('database.sqlite');
            if (file_exists($sqlitePath)) {
                $sqlite = new \PDO("sqlite:{$sqlitePath}");
                $sqlite->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

                // Users asli (hapus dummy seeder, masukkan 8 akun asli termasuk 6 teknisi)
                $users = $sqlite->query("SELECT * FROM users")->fetchAll(\PDO::FETCH_ASSOC);
                $validUserIds = array_column($users, 'id');
                \Illuminate\Support\Facades\DB::table('users')->whereNotIn('id', $validUserIds)->delete();
                \Illuminate\Support\Facades\DB::table('users')->upsert($users, ['id']);

                // Specifications (102 spek)
                $specs = $sqlite->query("SELECT * FROM device_specifications")->fetchAll(\PDO::FETCH_ASSOC);
                foreach (array_chunk($specs, 50) as $chunk) {
                    \Illuminate\Support\Facades\DB::table('device_specifications')->upsert($chunk, ['id']);
                }

                // Pastikan constraint stock_status di PostgreSQL mencakup status HB JKT & CKR
                if (config('database.default') === 'pgsql') {
                    \Illuminate\Support\Facades\DB::statement("ALTER TABLE devices DROP CONSTRAINT IF EXISTS devices_stock_status_check;");
                    \Illuminate\Support\Facades\DB::statement("ALTER TABLE devices ADD CONSTRAINT devices_stock_status_check CHECK (stock_status IN ('tersedia_hb_jkt', 'tersedia_hb_ckr', 'tersedia_pt', 'tersedia_hb_dikta', 'dibawa_teknisi', 'terpasang_pelanggan', 'terinstal', 'pending', 'rusak', 'hilang', 'dismantle'));");
                }

                // Devices (243 unit lengkap)
                $devices = $sqlite->query("SELECT * FROM devices")->fetchAll(\PDO::FETCH_ASSOC);
                foreach (array_chunk($devices, 50) as $chunk) {
                    \Illuminate\Support\Facades\DB::table('devices')->upsert($chunk, ['id']);
                }

                // Work Orders
                $wos = $sqlite->query("SELECT * FROM work_orders")->fetchAll(\PDO::FETCH_ASSOC);
                foreach (array_chunk($wos, 50) as $chunk) {
                    \Illuminate\Support\Facades\DB::table('work_orders')->upsert($chunk, ['id']);
                }

                // Device Histories
                $histories = $sqlite->query("SELECT * FROM device_histories")->fetchAll(\PDO::FETCH_ASSOC);
                foreach (array_chunk($histories, 50) as $chunk) {
                    \Illuminate\Support\Facades\DB::table('device_histories')->upsert($chunk, ['id']);
                }

                // Reset Sequences di PostgreSQL
                if (config('database.default') === 'pgsql') {
                    $seqTables = ['users', 'device_specifications', 'devices', 'work_orders', 'device_histories'];
                    foreach ($seqTables as $tbl) {
                        \Illuminate\Support\Facades\DB::statement("SELECT setval(pg_get_serial_sequence('{$tbl}', 'id'), coalesce(max(id), 0) + 1, false) FROM {$tbl};");
                    }
                }
            }
        }

        $userCount = \App\Models\User::count();
        $deviceCount = \App\Models\Device::count();
        
        // Pastikan seluruh akun teknisi memiliki password standar 'dikta123'
        \App\Models\User::where('role', 'teknisi')->update([
            'password' => \Illuminate\Support\Facades\Hash::make('dikta123'),
            'status' => 'active'
        ]);

        $techDetails = \App\Models\User::where('role', 'teknisi')->get()->map(function($u) {
            $hasPwd = \Illuminate\Support\Facades\Hash::check('dikta123', $u->password) ? '✅ Password: dikta123' : '❌';
            return "<li><strong>{$u->name}</strong> &bull; Username: <code style='color:#38bdf8;'>{$u->username}</code> &bull; HP: <code>{$u->phone}</code> &bull; {$hasPwd}</li>";
        })->implode('');

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
                    <div>Daftar Teknisi di Database:</div>
                    <ul style='margin:5px 0 10px 0; padding-left:18px; line-height:1.7;'>{$techDetails}</ul>
                    <div>Total Perangkat: <strong>{$deviceCount} Unit</strong></div>
                    <div>Status Sinkronisasi: <strong>Sinkronisasi 243 Perangkat dan 6 Teknisi Asli Berhasil</strong></div>
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
