<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use App\Models\Device;
use App\Models\WorkOrder;
use App\Models\DeviceHistory;

class DeviceAllocationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_can_allocate_device_directly_to_technician()
    {
        $admin = User::where('role', 'admin')->first();
        $technician = User::where('role', 'teknisi')->first();

        // Cari atau buat perangkat yang berstatus tersedia di HB Dikta
        $device = Device::whereIn('stock_status', ['tersedia_pt', 'tersedia_hb_dikta'])->first();
        if (!$device) {
            $device = Device::create([
                'registration_number' => 'REG-ALLOC-01',
                'serial_number' => 'SN-ALLOC-01',
                'name' => 'ONT Huawei Test Alokasi',
                'brand' => 'Huawei',
                'type' => 'ONT',
                'condition' => 'baru',
                'stock_status' => 'tersedia_hb_dikta',
                'current_location' => 'Gudang HB Dikta',
            ]);
        }

        $postData = [
            'technician_id' => $technician->id,
            'nojar' => 'NJR-ALOKASI-01',
            'activity' => 'PSB',
            'notes' => 'Alokasi langsung unit dari stok HB Dikta ke teknisi untuk pasang baru.',
        ];

        $response = $this->actingAs($admin)->post(route('admin.devices.allocate', $device), $postData);

        $response->assertSessionHas('success');
        $device->refresh();

        $this->assertEquals('dibawa_teknisi', $device->stock_status);
        $this->assertEquals($technician->id, $device->current_technician_id);
        $this->assertStringContainsString($technician->name, $device->current_location);
        $this->assertEquals('NJR-ALOKASI-01', $device->nojar);

        // Verify WorkOrder is updated or created
        $workOrder = WorkOrder::where('nojar', 'NJR-ALOKASI-01')->first();
        $this->assertNotNull($workOrder);
        $this->assertEquals($technician->id, $workOrder->technician_id);
        $this->assertEquals('sedang_dikerjakan', $workOrder->status);

        // Verify DeviceHistory
        $history = DeviceHistory::where('device_id', $device->id)->latest()->first();
        $this->assertNotNull($history);
        $this->assertEquals('Alokasi Stok HB Dikta ke Teknisi', $history->action);
        $this->assertEquals($technician->id, $history->technician_id);
    }

    public function test_allocation_buttons_visible_on_stock_and_dashboard_pages()
    {
        $admin = User::where('role', 'admin')->first();

        // Pastikan ada perangkat di HB Dikta
        $device = Device::whereIn('stock_status', ['tersedia_pt', 'tersedia_hb_dikta'])->first();
        $this->assertNotNull($device);

        // Cek halaman Stok HB Dikta
        $stockResponse = $this->actingAs($admin)->get(route('admin.devices.stock'));
        $stockResponse->assertStatus(200);
        $stockResponse->assertSee('Alokasikan');
        $stockResponse->assertSee('Daftar Unit Tersedia di Gudang HB Dikta');

        // Cek halaman Dashboard Utama dengan filter tersedia_hb_dikta
        $dashboardResponse = $this->actingAs($admin)->get(route('admin.dashboard', ['status' => 'tersedia_hb_dikta']));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Alokasikan');

        // Cek halaman Detail Perangkat
        $detailResponse = $this->actingAs($admin)->get(route('admin.devices.show', $device));
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('Alokasikan ke Teknisi');
    }

    public function test_stock_page_has_customer_address_columns_and_search_feature()
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get(route('admin.devices.stock'));
        $response->assertStatus(200);

        // Pastikan kolom No. Registrasi, Nama Perangkat, dan Area muncul di tabel
        $response->assertSee('No. Registrasi');
        $response->assertSee('Nama Perangkat');
        $response->assertSee('Area');
        $response->assertDontSee('<th class="px-5 py-3.5">Pelanggan</th>', false);
        $response->assertDontSee('<th class="px-5 py-3.5">Alamat</th>', false);
        $response->assertDontSee('<th class="px-5 py-3.5">Lokasi Penyimpanan</th>', false);
        $response->assertDontSee('<th class="px-5 py-3.5">Serial Number</th>', false);
        $response->assertDontSee('<th class="px-5 py-3.5">Kondisi</th>', false);
        $response->assertDontSee('Rekapitulasi Kategori Perangkat');

        // Pastikan input pencarian No. Registrasi / Nojar ada
        $response->assertSee('Cari dengan No.registrasi / Nojar');
        $response->assertSee('name="search"', false);
    }

    public function test_stock_page_search_filters_devices_by_registration_number()
    {
        $admin = User::where('role', 'admin')->first();

        // Buat dua perangkat khusus untuk pengetesan pencarian
        $deviceA = Device::create([
            'registration_number' => 'REG-FIND-ABC',
            'serial_number' => 'SN-FIND-ABC',
            'name' => 'Device Khusus ABC',
            'brand' => 'Huawei',
            'type' => 'ONT',
            'condition' => 'baru',
            'stock_status' => 'tersedia_hb_dikta',
            'customer_name' => 'Klien PT Alpha',
            'address' => 'Jl. Sudirman No. 10',
            'current_location' => 'Gudang HB Dikta',
        ]);

        $deviceB = Device::create([
            'registration_number' => 'REG-FIND-XYZ',
            'serial_number' => 'SN-FIND-XYZ',
            'name' => 'Device Khusus XYZ',
            'brand' => 'ZTE',
            'type' => 'Router',
            'condition' => 'baru',
            'stock_status' => 'tersedia_hb_dikta',
            'customer_name' => 'Klien PT Beta',
            'address' => 'Jl. Thamrin No. 20',
            'current_location' => 'Gudang HB Dikta',
        ]);

        // Cari dengan query REG-FIND-ABC
        $searchResponse = $this->actingAs($admin)->get(route('admin.devices.stock', ['search' => 'REG-FIND-ABC']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('REG-FIND-ABC');
        $searchResponse->assertSee('Klien PT Alpha');
        $searchResponse->assertDontSee('REG-FIND-XYZ');

        // Cari dengan query nama pelanggan
        $searchCustomer = $this->actingAs($admin)->get(route('admin.devices.stock', ['search' => 'PT Beta']));
        $searchCustomer->assertStatus(200);
        $searchCustomer->assertSee('REG-FIND-XYZ');
        $searchCustomer->assertSee('Klien PT Beta');
        $searchCustomer->assertDontSee('REG-FIND-ABC');
    }

    public function test_admin_can_allocate_with_customer_and_address_from_stock()
    {
        $admin = User::where('role', 'admin')->first();
        $technician = User::where('role', 'teknisi')->first();

        $device = Device::create([
            'registration_number' => 'REG-CUST-99',
            'serial_number' => 'SN-CUST-99',
            'name' => 'GPOn Baru HB Dikta',
            'brand' => 'Fiberhome',
            'type' => 'ONT',
            'condition' => 'baru',
            'stock_status' => 'tersedia_hb_dikta',
            'current_location' => 'Gudang HB Dikta',
        ]);

        $postData = [
            'technician_id' => $technician->id,
            'nojar' => 'NJR-CUST-99',
            'customer_name' => 'Bank Mandiri Cabang Utama',
            'address' => 'Jl. Gatot Subroto Kav. 36-38, Jakarta',
            'activity' => 'PSB',
            'notes' => 'Alokasi stok HB Dikta lengkap data pelanggan.',
        ];

        $response = $this->actingAs($admin)->post(route('admin.devices.allocate', $device), $postData);
        $response->assertSessionHas('success');

        $device->refresh();
        $this->assertEquals('dibawa_teknisi', $device->stock_status);
        $this->assertEquals('Bank Mandiri Cabang Utama', $device->customer_name);
        $this->assertEquals('Jl. Gatot Subroto Kav. 36-38, Jakarta', $device->address);
        $this->assertEquals('NJR-CUST-99', $device->nojar);

        // Verify WorkOrder
        $workOrder = WorkOrder::where('nojar', 'NJR-CUST-99')->first();
        $this->assertNotNull($workOrder);
        $this->assertEquals('Bank Mandiri Cabang Utama', $workOrder->customer_name);
        $this->assertEquals('Jl. Gatot Subroto Kav. 36-38, Jakarta', $workOrder->location_address);
    }
}
