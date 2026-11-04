<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use App\Models\Device;
use App\Models\WorkOrder;

class DashboardAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_dashboard_displays_all_8_columns()
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin, 'Admin user must exist');

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Total Semua Perangkat');
        $response->assertSee('Tersedia di HB JKT');
        $response->assertSee('Tersedia di HB CKR');
        $response->assertSee('Dibawa Teknisi');
        $response->assertSee('Terinstal');
        $response->assertSee('Pending');
        $response->assertSee('Rusak');
        $response->assertSee('Hilang');
    }

    public function test_admin_dashboard_filter_by_status()
    {
        $admin = User::where('role', 'admin')->first();

        // Verifikasi bahwa seluruh 8 kartu metrik berwujud link yang dapat diklik
        $dashboard = $this->actingAs($admin)->get('/admin/dashboard');
        $dashboard->assertStatus(200);
        $dashboard->assertSee('status=all');
        $dashboard->assertSee('status=tersedia_hb_jkt');
        $dashboard->assertSee('status=tersedia_hb_ckr');
        $dashboard->assertSee('status=dibawa_teknisi');
        $dashboard->assertSee('status=terinstal');
        $dashboard->assertSee('status=pending');
        $dashboard->assertSee('status=rusak');
        $dashboard->assertSee('status=hilang');

        // Verifikasi setiap filter status dapat diakses dan menampilkan data
        foreach (['all', 'tersedia_hb_jkt', 'tersedia_hb_ckr', 'dibawa_teknisi', 'terinstal', 'pending', 'rusak', 'hilang'] as $status) {
            $response = $this->actingAs($admin)->get('/admin/dashboard?status=' . $status);
            $response->assertStatus(200);
        }
    }

    public function test_admin_dashboard_redundant_buttons_removed()
    {
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Tambah Perangkat Baru');
        // Tombol redundant di dashboard utama harus tidak ada
        $response->assertDontSee('Scan Barcode / Cari No. Reg');
        $response->assertDontSee('Rekap Stok HB Dikta');
        $response->assertDontSee('+ Tambah Teknisi');
    }

    public function test_admin_scan_page_and_sidebar_item_removed()
    {
        $admin = User::where('role', 'admin')->first();

        // Menu "Scan & Cari No. Reg" tidak ada lagi di sidebar
        $dashboardResponse = $this->actingAs($admin)->get('/admin/dashboard');
        $dashboardResponse->assertDontSee('Scan & Cari No. Reg');

        // Tombol Scan Barcode tidak ada di halaman data perangkat
        $devicesResponse = $this->actingAs($admin)->get('/admin/devices');
        $devicesResponse->assertDontSee('Scan Barcode');

        // Route /admin/scan harus 404 (sudah dihilangkan)
        $scanResponse = $this->actingAs($admin)->get('/admin/scan');
        $scanResponse->assertStatus(404);
    }


    public function test_technician_my_devices_page_has_actions()
    {
        // Cari teknisi yang membawa perangkat (seperti Agus atau Rian dari seeder)
        $tech = User::where('role', 'teknisi')->whereHas('devices')->first();
        if (!$tech) {
            $tech = User::where('role', 'teknisi')->first();
            $device = Device::first();
            $device->current_technician_id = $tech->id;
            $device->stock_status = 'dibawa_teknisi';
            $device->save();
        }

        $response = $this->actingAs($tech)->get('/technician/my-devices');
        $response->assertStatus(200);
        $response->assertSee('Kembalikan');
    }

    public function test_technician_can_report_device_lost()
    {
        $device = Device::where('stock_status', 'dibawa_teknisi')->whereNotNull('current_technician_id')->first();
        $this->assertNotNull($device);
        $tech = User::find($device->current_technician_id);

        $response = $this->actingAs($tech)->from('/technician/my-devices')->post("/technician/devices/{$device->id}/report-lost", [
            'notes' => 'Jatuh saat perjalanan kembali ke kantor'
        ]);

        $response->assertRedirect('/technician/my-devices');
        $device->refresh();
        $this->assertEquals('hilang', $device->stock_status);
        $this->assertNull($device->current_technician_id);
    }

    public function test_technician_can_report_device_broken()
    {
        $device = Device::where('stock_status', 'dibawa_teknisi')->whereNotNull('current_technician_id')->first();
        $this->assertNotNull($device);
        $tech = User::find($device->current_technician_id);

        $response = $this->actingAs($tech)->from('/technician/my-devices')->post("/technician/devices/{$device->id}/report-broken", [
            'notes' => 'Port optik pecah'
        ]);

        $response->assertRedirect('/technician/my-devices');
        $device->refresh();
        $this->assertEquals('rusak', $device->stock_status);
        $this->assertNull($device->current_technician_id);
    }

    public function test_technician_can_submit_task_as_pending_and_device_becomes_pending()
    {
        $task = WorkOrder::where('status', 'belum_dikerjakan')->whereNotNull('device_id')->whereNotNull('technician_id')->first();
        if (!$task) {
            $task = WorkOrder::first();
        }
        $this->assertNotNull($task);

        $tech = User::find($task->technician_id);
        $device = Device::find($task->device_id);

        // Simulasi teknisi submit pekerjaan sebagai pending karena kendala di lapangan
        $response = $this->actingAs($tech)->post("/technician/tasks/{$task->id}/submit-complete", [
            'status' => 'pending',
            'status_notes' => 'Pelanggan belum ada di rumah, dijadwal ulang besok.',
        ]);

        $response->assertRedirect("/technician/tasks/{$task->id}");
        $task->refresh();
        $this->assertEquals('pending', $task->status);

        if ($device) {
            $device->refresh();
            $this->assertEquals('pending', $device->stock_status);
        }
    }

    public function test_admin_devices_index_table_removes_serial_number_and_condition_columns()
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get(route('admin.devices.index'));
        $response->assertStatus(200);

        // Header Serial Number dan Kondisi tidak boleh ada di tabel
        $response->assertDontSee('<th class="px-6 py-3.5">Serial Number</th>', false);
        $response->assertDontSee('<th class="px-6 py-3.5">Kondisi</th>', false);

        // Header yang harus ada
        $response->assertSee('Nomor Registrasi');
        $response->assertSee('Nama Perangkat', false);
        $response->assertSee('Status Stok');
        $response->assertSee('Posisi / Pemegang Terkini');
        $response->assertSee('Aksi');
    }

    public function test_admin_technicians_page_removes_numbers_in_names_and_removes_job_columns()
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get(route('admin.technicians.index'));
        $response->assertStatus(200);

        // Keterangan (Teknisi 1), (Teknisi 2), (Teknisi 3) tidak boleh ada
        $response->assertDontSee('(Teknisi 1)');
        $response->assertDontSee('(Teknisi 2)');
        $response->assertDontSee('(Teknisi 3)');

        // Nama bersih harus tampil
        $response->assertSee('Budi Santoso');
        $response->assertSee('Agus Pratama');
        $response->assertSee('Rian Hidayat');

        // Kolom Tugas Aktif dan Tugas Selesai tidak boleh ada
        $response->assertDontSee('<th class="px-6 py-3.5 text-center">Tugas Aktif</th>', false);
        $response->assertDontSee('<th class="px-6 py-3.5 text-center">Tugas Selesai</th>', false);

        // Fitur popup modal perangkat dibawa harus ada
        $response->assertSee('Perangkat Dibawa');
        $response->assertSee('technicianCarriedDevicesModal');
    }

    public function test_admin_device_show_and_print_label_has_linear_barcode_and_fields()
    {
        $admin = User::where('role', 'admin')->first();
        $device = Device::first();

        // 1. Detail Perangkat (show)
        $response = $this->actingAs($admin)->get(route('admin.devices.show', $device));
        $response->assertStatus(200);

        // Barcode linier dan tombol Cetak Label dihapus dari halaman detail perangkat sesuai permintaan user
        $response->assertDontSee('Cetak Label Barcode');
        $response->assertDontSee('https://api.qrserver.com/v1/create-qr-code/');

        // Harus tidak ada Serial Number (SN) di info detail
        $response->assertDontSee('Serial Number (SN)');
        // Harus tidak ada Merk / Tipe di info detail
        $response->assertDontSee('Merk / Tipe');
        // Harus tidak ada Kondisi Fisik
        $response->assertDontSee('Kondisi Fisik');

        // Harus ada Alamat
        $response->assertSee('Alamat');

        // 2. Cetak Label (print-label)
        $printResponse = $this->actingAs($admin)->get(route('admin.devices.print-label', $device));
        $printResponse->assertStatus(200);
        $printResponse->assertSee('role="img"', false);
        $printResponse->assertDontSee('https://api.qrserver.com/v1/create-qr-code/');
        $printResponse->assertDontSee('Serial No:');
        $printResponse->assertDontSee('Merk/Model:');
        $printResponse->assertDontSee('Kondisi:');
        $printResponse->assertSee('Alamat:');
    }

    public function test_device_table_replaces_print_and_edit_icons_with_evidence_and_coordinator_removes_evidence()
    {
        $admin = User::where('role', 'admin')->first();
        $coord = User::where('role', 'koordinator')->first();
        $device = Device::first();

        // 1. Pada halaman Data Perangkat Admin:
        $response = $this->actingAs($admin)->get(route('admin.devices.index'));
        $response->assertStatus(200);

        // Ikon Cetak Label Barcode & Ubah Data tidak boleh ada di tabel
        $response->assertDontSee('title="Cetak Label Barcode"', false);
        $response->assertDontSee('title="Ubah Data"', false);

        // Tombol Bukti Foto / Evidence harus ada
        $response->assertSee('title="Bukti Foto / Evidence"', false);
        $response->assertSee('id="deviceEvidenceModal"', false);

        // 2. Endpoint Evidence Perangkat Admin berfungsi
        $evidenceView = $this->actingAs($admin)->get(route('admin.devices.evidence', $device));
        $evidenceView->assertStatus(200);
        $evidenceView->assertSee('Bukti Foto / Evidence');

        $evidenceJson = $this->actingAs($admin)->getJson(route('admin.devices.evidence', $device));
        $evidenceJson->assertStatus(200);
        $evidenceJson->assertJsonStructure(['success', 'device', 'evidence']);

        // 3. Pada Koordinator:
        // Menu "Bukti Foto / Evidence" tidak ada lagi di sidebar
        $coordResponse = $this->actingAs($coord)->get(route('coordinator.dashboard'));
        $coordResponse->assertDontSee('Bukti Foto / Evidence');

        // Route /coordinator/evidence harus 404 (sudah dipindahkan ke Admin)
        $coordEvidence = $this->actingAs($coord)->get('/coordinator/evidence');
        $coordEvidence->assertStatus(404);
    }

    public function test_return_to_lintasarta_action_removes_device()
    {
        $admin = User::where('role', 'admin')->first();
        $device = Device::first();

        // 1. Check index removes "Dikembalikan ke Lintasarta" column and filter
        $response = $this->actingAs($admin)->get(route('admin.devices.index'));
        $response->assertStatus(200);
        $response->assertDontSee('<th class="px-6 py-3.5">Dikembalikan ke Lintasarta</th>', false);
        $response->assertDontSee('Semua Lintasarta');
        
        // 2. Check index has Kembalikan button in table row beside location and removed from dashboard sidebar
        $response->assertSee('title="Kembalikan ke Lintasarta (Otomatis Dikeluarkan)"', false);
        $response->assertSee('Kembalikan');
        $response->assertDontSee('Kembalikan Perangkat');

        // 3. Test submitting return to Lintasarta via process-return from index
        $deviceId = $device->id;
        $returnResponse = $this->actingAs($admin)
            ->from(route('admin.devices.index'))
            ->post(
                route('admin.devices.process-return'),
                ['device_id' => $deviceId, 'destination' => 'Gudang Tekno', 'notes' => 'Pengembalian inventaris']
            );
        $returnResponse->assertRedirect(route('admin.devices.index'));

        // 4. Device is automatically deleted from database (data perangkat hilang)
        $this->assertDatabaseMissing('devices', ['id' => $deviceId]);
    }

    public function test_area_column_and_update()
    {
        $admin = User::where('role', 'admin')->first();
        $device = Device::first();
        $device->update(['area' => 'SEOA']);

        // 1. Check index displays "Area" column, filter, and area value
        $response = $this->actingAs($admin)->get(route('admin.devices.index'));
        $response->assertStatus(200);
        $response->assertSee('<th class="px-6 py-3.5">Area</th>', false);
        $response->assertSee('Semua Area');
        $response->assertSee('SEOA');

        // 2. Test filter by Area
        $filterResponse = $this->actingAs($admin)->get(route('admin.devices.index', ['area' => 'SEOA']));
        $filterResponse->assertStatus(200);
        $filterResponse->assertSee($device->registration_number);

        // 3. Create form renders Area dropdown
        $createResponse = $this->actingAs($admin)->get(route('admin.devices.create'));
        $createResponse->assertStatus(200);
        $createResponse->assertSee('name="area"', false);

        // 4. Edit form renders Area dropdown
        $editResponse = $this->actingAs($admin)->get(route('admin.devices.edit', $device));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('name="area"', false);
    }

    public function test_admin_dashboard_terinstal_has_search_feature_by_registration_and_nojar()
    {
        $admin = User::where('role', 'admin')->first();

        // 1. Visit dashboard with status=terinstal
        $response = $this->actingAs($admin)->get(route('admin.dashboard', ['status' => 'terinstal']));
        $response->assertStatus(200);

        // Check search bar exists with requested placeholder
        $response->assertSee('placeholder="Cari dengan No.registrasi / Nojar"', false);

        // Also check search bar exists for other statuses
        $responseAll = $this->actingAs($admin)->get(route('admin.dashboard', ['status' => 'all']));
        $responseAll->assertSee('placeholder="Cari dengan No.registrasi / Nojar"', false);

        $responseTeknisi = $this->actingAs($admin)->get(route('admin.dashboard', ['status' => 'dibawa_teknisi']));
        $responseTeknisi->assertSee('placeholder="Cari dengan No.registrasi / Nojar"', false);

        $responseWarehouse = $this->actingAs($admin)->get(route('admin.dashboard', ['status' => 'tersedia_hb_dikta']));
        $responseWarehouse->assertSee('placeholder="Cari dengan No.registrasi / Nojar"', false);

        // 2. Create device with terinstal status and distinct nojar
        $device = Device::create([
            'registration_number' => 'REG-INSTALLED-999',
            'serial_number' => 'SN-INST-999',
            'name' => 'Router Terinstal Test',
            'brand' => 'MikroTik',
            'type' => 'Router',
            'stock_status' => 'terinstal',
            'nojar' => 'NJR-SPECIFIC-888',
            'customer_name' => 'PT Pelanggan Terinstal',
            'current_location' => 'Terpasang di Lokasi Pelanggan',
        ]);

        // Search by registration number
        $searchRegResponse = $this->actingAs($admin)->get(route('admin.dashboard', [
            'status' => 'terinstal',
            'search' => 'REG-INSTALLED-999'
        ]));
        $searchRegResponse->assertStatus(200);
        $searchRegResponse->assertSee('REG-INSTALLED-999');
        $searchRegResponse->assertSee('Router Terinstal Test');

        // Search by nojar
        $searchNojarResponse = $this->actingAs($admin)->get(route('admin.dashboard', [
            'status' => 'terinstal',
            'search' => 'NJR-SPECIFIC-888'
        ]));
        $searchNojarResponse->assertStatus(200);
        $searchNojarResponse->assertSee('REG-INSTALLED-999');
        $searchNojarResponse->assertSee('NJR-SPECIFIC-888');
    }
}


