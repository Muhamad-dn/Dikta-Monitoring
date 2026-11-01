<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use App\Models\Device;
use App\Models\WorkOrder;

class DeviceRegistrationFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_device_create_page_renders_all_required_elements()
    {
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get(route('admin.devices.create'));

        $response->assertStatus(200);
        $response->assertSee('Scan Linier Barcode Perangkat');
        $response->assertSee('Masukkan Registrasi Manual');
        $response->assertDontSee('Foto / Upload Barcode');
        $response->assertSee('linear-barcode-reader');
        $response->assertSee('Nomor Registrasi');
        $response->assertSee('Nama Perangkat');
        $response->assertSee('NOJAR');
        $response->assertSee('Pelanggan');
        $response->assertSee('Alamat');
        $response->assertSee('Aktivitas');
        $response->assertDontSee('value="Survey"', false);
        $response->assertSee('PSB');
        $response->assertSee('Troubleshoot');
        $response->assertSee('Simulasi');
        $response->assertSee('Spare Dikta');
        $response->assertSee('Catatan Tambahan');
        // Memastikan keterangan bawah di setiap kolom telah dihapus sesuai permintaan user
        $response->assertDontSee('Nomor unik barcode / penomoran inventaris PT DIKTA.');
        $response->assertDontSee('Nama model perangkat keras jaringan.');
        $response->assertDontSee('Kode tiket pekerjaan jaringan terkait unit ini.');
        $response->assertDontSee('Nama perorangan atau instansi pelanggan.');
        $response->assertDontSee('Alamat lengkap lokasi pemasangan / operasional perangkat.');
    }

    public function test_activity_descriptions_are_removed_and_technician_list_is_present()
    {
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get(route('admin.devices.create'));

        $response->assertStatus(200);

        // Keterangan sub-deskripsi aktivitas harus dihilangkan sesuai permintaan user
        $response->assertDontSee('Pengecekan Jalur / ODP');
        $response->assertDontSee('Pasang Sambungan Baru');
        $response->assertDontSee('Perbaikan / Penggantian');
        $response->assertDontSee('Uji Coba Lab / Setting');

        // Kolom teknisi dan daftar teknisi harus ada
        $response->assertSee('Teknisi');
        $technicians = User::where('role', 'teknisi')->get();
        foreach ($technicians as $tech) {
            $response->assertSee($tech->name);
        }
    }

    public function test_device_registration_with_all_new_fields_succeeds()
    {
        $admin = User::where('role', 'admin')->first();

        $postData = [
            'registration_number' => 'REG-NEW-999',
            'serial_number' => 'SN-HW-TEST-999',
            'name' => 'ONT Huawei HG8245H5 GPON Dual Band',
            'nojar' => 'NJR999',
            'customer_name' => 'PT Pelanggan Sejahtera',
            'address' => 'Jl. Boulevard No. 45, Jakarta Selatan',
            'activity' => 'PSB',
            'notes' => 'Adaptor original + patchcord 3m disertakan.',
        ];

        $response = $this->actingAs($admin)->post(route('admin.devices.store'), $postData);

        $device = Device::where('registration_number', 'REG-NEW-999')->first();
        $this->assertNotNull($device, 'Device should be created in database');
        $this->assertEquals('SN-HW-TEST-999', $device->serial_number);
        $this->assertEquals('Huawei', $device->brand);
        $this->assertEquals('ONT', $device->type);
        $this->assertEquals('NJR999', $device->nojar);
        $this->assertEquals('PT Pelanggan Sejahtera', $device->customer_name);
        $this->assertEquals('Jl. Boulevard No. 45, Jakarta Selatan', $device->address);
        $this->assertEquals('PSB', $device->activity);
        $this->assertEquals('tersedia_pt', $device->stock_status);
        $this->assertNull($device->current_technician_id);

        // Verify WorkOrder is automatically linked
        $workOrder = WorkOrder::where('nojar', 'NJR999')->first();
        $this->assertNotNull($workOrder, 'WorkOrder should be auto-created/updated for the nojar');
        $this->assertEquals('Instalasi', $workOrder->category);
        $this->assertEquals('PT Pelanggan Sejahtera', $workOrder->customer_name);
        $this->assertEquals($device->id, $workOrder->device_id);

        $response->assertRedirect(route('admin.devices.show', $device));
    }

    public function test_device_registration_with_technician_allocated_sets_status_dibawa_teknisi()
    {
        $admin = User::where('role', 'admin')->first();
        $tech = User::where('role', 'teknisi')->first();
        $this->assertNotNull($tech);

        $postData = [
            'registration_number' => 'REG-TECH-888',
            'serial_number' => 'SN-TECH-888',
            'name' => 'ONT ZTE F609 GPON',
            'nojar' => 'NJR888',
            'customer_name' => 'Bapak Hendra',
            'address' => 'Jl. Gatot Subroto No. 88',
            'activity' => 'PSB',
            'technician_id' => $tech->id,
            'notes' => 'Langsung dibawa oleh teknisi untuk eksekusi PSB hari ini.',
        ];

        $response = $this->actingAs($admin)->post(route('admin.devices.store'), $postData);

        $device = Device::where('registration_number', 'REG-TECH-888')->first();
        $this->assertNotNull($device);
        $this->assertEquals('dibawa_teknisi', $device->stock_status);
        $this->assertEquals($tech->id, $device->current_technician_id);
        $this->assertStringContainsString($tech->name, $device->current_location);

        // Verify WorkOrder has technician assigned
        $workOrder = WorkOrder::where('nojar', 'NJR888')->first();
        $this->assertNotNull($workOrder);
        $this->assertEquals($tech->id, $workOrder->technician_id);
        $this->assertEquals('sedang_dikerjakan', $workOrder->status);

        $response->assertRedirect(route('admin.devices.show', $device));
    }

    public function test_device_registration_with_troubleshoot_activity()
    {
        $admin = User::where('role', 'admin')->first();

        $postData = [
            'registration_number' => 'REG-TRB-101',
            'serial_number' => 'SN-TRB-101',
            'name' => 'Router MikroTik RB750Gr3',
            'nojar' => 'NJR101',
            'customer_name' => 'Ibu Ratna',
            'address' => 'Komplek Permata Hijau Blok C2',
            'activity' => 'Troubleshoot',
            'notes' => 'Perbaikan koneksi pelanggan.',
        ];

        $response = $this->actingAs($admin)->post(route('admin.devices.store'), $postData);

        $device = Device::where('registration_number', 'REG-TRB-101')->first();
        $this->assertNotNull($device);
        $this->assertEquals('Troubleshoot', $device->activity);
        $this->assertEquals('MikroTik', $device->brand);
        $this->assertEquals('Router', $device->type);

        $workOrder = WorkOrder::where('nojar', 'NJR101')->first();
        $this->assertNotNull($workOrder);
        $this->assertEquals('Troubleshooting', $workOrder->category);
    }

    public function test_device_registration_with_spare_dikta_activity_does_not_require_nojar_pelanggan_alamat()
    {
        $admin = User::where('role', 'admin')->first();

        $postData = [
            'registration_number' => 'REG-SPARE-001',
            'name' => 'ONT Huawei HG8245H5 GPON (Cadangan)',
            'activity' => 'Spare Dikta',
            'notes' => 'Unit cadangan siap pakai.',
        ];

        $response = $this->actingAs($admin)->post(route('admin.devices.store'), $postData);

        $device = Device::where('registration_number', 'REG-SPARE-001')->first();
        $this->assertNotNull($device);
        $this->assertEquals('Spare Dikta', $device->activity);
        $this->assertNull($device->nojar);
        $this->assertNull($device->customer_name);
        $this->assertNull($device->address);
        $this->assertEquals('tersedia_pt', $device->stock_status);
        $this->assertEquals('Gudang HB Dikta (Stok Spare)', $device->current_location);

        // Tidak boleh ada WorkOrder yang dibuat / dikaitkan dengan perangkat Spare Dikta
        $this->assertEquals(0, WorkOrder::where('device_id', $device->id)->count());

        $response->assertRedirect(route('admin.devices.show', $device));
    }

    public function test_reallocating_existing_stock_device_to_technician_automatically_moves_device_and_creates_work_order()
    {
        $admin = User::where('role', 'admin')->first();
        $tech = User::where('role', 'teknisi')->first();

        // 1. Buat perangkat yang awalnya hanya stok (Spare Dikta di gudang)
        $stockDevice = Device::create([
            'registration_number' => 'REG-STK-777',
            'serial_number' => 'SN-STK-777',
            'name' => 'ONT Huawei HG8245H5 Stok',
            'brand' => 'Huawei',
            'type' => 'ONT',
            'stock_status' => 'tersedia_pt',
            'condition' => 'baru',
            'current_location' => 'Gudang HB Dikta (Stok Spare)',
            'activity' => 'Spare Dikta',
        ]);

        $initialDeviceCount = Device::count();

        // 2. Sekarang admin menginput ulang nomor registrasi yang sama di Form Registrasi,
        // tapi langsung memilih teknisi dan mengisi NOJAR serta pelanggan!
        $reallocationData = [
            'registration_number' => 'REG-STK-777',
            'name' => 'ONT Huawei HG8245H5 Stok',
            'activity' => 'PSB',
            'nojar' => 'NJR-PINDAH-777',
            'customer_name' => 'Bapak Joko Purnomo',
            'address' => 'Jl. Kebon Jeruk No. 12',
            'technician_id' => $tech->id,
            'notes' => 'Unit diambil dari stok cadangan untuk pasang baru.',
        ];

        $response = $this->actingAs($admin)->post(route('admin.devices.store'), $reallocationData);

        // 3. Pastikan tidak ada duplikasi data (jumlah device tetap sama)
        $this->assertEquals($initialDeviceCount, Device::count());

        // 4. Pastikan data perangkat otomatis berpindah ke teknisi
        $stockDevice->refresh();
        $this->assertEquals('dibawa_teknisi', $stockDevice->stock_status);
        $this->assertEquals($tech->id, $stockDevice->current_technician_id);
        $this->assertStringContainsString($tech->name, $stockDevice->current_location);
        $this->assertEquals('PSB', $stockDevice->activity);
        $this->assertEquals('NJR-PINDAH-777', $stockDevice->nojar);
        $this->assertEquals('Bapak Joko Purnomo', $stockDevice->customer_name);
        $this->assertEquals('Jl. Kebon Jeruk No. 12', $stockDevice->address);

        // 5. Pastikan WorkOrder tiket otomatis terbuat dan teralokasi ke teknisi
        $workOrder = WorkOrder::where('nojar', 'NJR-PINDAH-777')->first();
        $this->assertNotNull($workOrder);
        $this->assertEquals($tech->id, $workOrder->technician_id);
        $this->assertEquals($stockDevice->id, $workOrder->device_id);
        $this->assertEquals('sedang_dikerjakan', $workOrder->status);

        $response->assertRedirect(route('admin.devices.show', $stockDevice));
    }

    public function test_check_registration_api_endpoint()
    {
        $admin = User::where('role', 'admin')->first();

        Device::create([
            'registration_number' => 'REG-LOOKUP-123',
            'serial_number' => 'SN-LOOKUP-123',
            'name' => 'Router MikroTik Hex',
            'brand' => 'MikroTik',
            'type' => 'Router',
            'stock_status' => 'tersedia_pt',
            'condition' => 'baru',
            'current_location' => 'Gudang HB Dikta',
            'activity' => 'Spare Dikta',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.devices.check-registration', ['reg' => 'REG-LOOKUP-123']));
        $response->assertStatus(200);
        $response->assertJson([
            'exists' => true,
            'registration_number' => 'REG-LOOKUP-123',
            'name' => 'Router MikroTik Hex',
            'stock_status' => 'tersedia_pt',
        ]);
    }

    public function test_typed_registration_immediately_finds_bwa_stock_devices()
    {
        $admin = User::where('role', 'admin')->first();

        // 1. Cek exact typing
        $response1 = $this->actingAs($admin)->get(route('admin.devices.check-registration', ['reg' => 'B2WN0286541MA0001']));
        $response1->assertStatus(200);
        $response1->assertJson([
            'exists' => true,
            'name' => 'Fortinet 200F',
            'registration_number' => 'B2WN0286541MA0001',
            'area' => 'BWA',
            'stock_status' => 'tersedia_pt',
        ]);

        // 2. Cek typing RC
        $response2 = $this->actingAs($admin)->get(route('admin.devices.check-registration', ['reg' => 'B2WN01AH020402300']));
        $response2->assertStatus(200);
        $response2->assertJson([
            'exists' => true,
            'name' => 'RC',
            'registration_number' => 'B2WN01AH020402300',
            'area' => 'BWA',
        ]);

        // 3. Cek typing partial / suffix MA0001
        $response3 = $this->actingAs($admin)->get(route('admin.devices.check-registration', ['reg' => 'MA0001']));
        $response3->assertStatus(200);
        $response3->assertJson([
            'exists' => true,
            'name' => 'Fortinet 200F',
        ]);
    }
}
