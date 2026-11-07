<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use App\Models\Device;

class LinearBarcodeScanStockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_stock_page_no_longer_renders_linear_barcode_scanner_elements()
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get(route('admin.devices.stock'));

        $response->assertStatus(200);
        $response->assertDontSee('Scan Barcode Linier');
        $response->assertDontSee('stock-linear-barcode-reader');
        $response->assertSee('stockAllocationApp()');
    }

    public function test_check_registration_matches_registration_number_and_serial_number()
    {
        $admin = User::where('role', 'admin')->first();

        $device = Device::create([
            'registration_number' => 'REG-SCAN-999',
            'serial_number' => 'SN-SCAN-888',
            'name' => 'ZTE F609 Barcode Test',
            'brand' => 'ZTE',
            'type' => 'ONT',
            'condition' => 'baru',
            'stock_status' => 'tersedia_hb_dikta',
            'current_location' => 'Gudang HB Dikta',
        ]);

        // Cek via registration_number
        $response1 = $this->actingAs($admin)->getJson(route('admin.devices.check-registration', ['reg' => 'REG-SCAN-999']));
        $response1->assertStatus(200);
        $response1->assertJson([
            'exists' => true,
            'id' => $device->id,
            'registration_number' => 'REG-SCAN-999',
            'serial_number' => 'SN-SCAN-888',
            'is_in_warehouse' => true,
        ]);

        // Cek via serial_number (barcode SN)
        $response2 = $this->actingAs($admin)->getJson(route('admin.devices.check-registration', ['reg' => 'SN-SCAN-888']));
        $response2->assertStatus(200);
        $response2->assertJson([
            'exists' => true,
            'id' => $device->id,
            'registration_number' => 'REG-SCAN-999',
            'serial_number' => 'SN-SCAN-888',
            'is_in_warehouse' => true,
        ]);
    }

    public function test_check_registration_returns_not_in_warehouse_when_device_with_technician()
    {
        $admin = User::where('role', 'admin')->first();
        $technician = User::where('role', 'teknisi')->first();

        $device = Device::create([
            'registration_number' => 'REG-FIELD-111',
            'serial_number' => 'SN-FIELD-222',
            'name' => 'Fiberhome HG680P',
            'brand' => 'Fiberhome',
            'type' => 'STB',
            'condition' => 'bekas_bagus',
            'stock_status' => 'dibawa_teknisi',
            'current_technician_id' => $technician->id,
            'current_location' => 'Dibawa oleh Teknisi: ' . $technician->name,
        ]);

        $response = $this->actingAs($admin)->getJson(route('admin.devices.check-registration', ['reg' => 'REG-FIELD-111']));
        $response->assertStatus(200);
        $response->assertJson([
            'exists' => true,
            'id' => $device->id,
            'registration_number' => 'REG-FIELD-111',
            'is_in_warehouse' => false,
            'current_technician_name' => $technician->name,
        ]);
    }

    public function test_check_registration_returns_false_when_barcode_not_found()
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->getJson(route('admin.devices.check-registration', ['reg' => 'UNKNOWN-BARCODE-9999']));
        $response->assertStatus(200);
        $response->assertJson([
            'exists' => false,
        ]);
    }

    public function test_create_device_prefills_scanned_barcode_from_query()
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get(route('admin.devices.create', ['reg' => 'BARCODE-SCANNED-123']));
        $response->assertStatus(200);
        $response->assertSee('BARCODE-SCANNED-123');
    }

    public function test_check_registration_matches_lintasarta_full_barcode_to_extracted_reg_number()
    {
        $admin = User::where('role', 'admin')->first();

        // Misalkan unit didaftarkan dengan no registrasi HA0267 (5-6 karakter terakhir barcode)
        $device = Device::create([
            'registration_number' => 'HA0267',
            'serial_number' => 'B2WN0262020HA0267',
            'name' => 'Fortigate 50E Lintasarta',
            'brand' => 'Fortinet',
            'type' => 'Router',
            'condition' => 'baru',
            'stock_status' => 'tersedia_hb_dikta',
            'current_location' => 'Gudang HB Dikta',
        ]);

        // Scan barcode fisik lengkap: B2WN0262020HA0267
        $response = $this->actingAs($admin)->getJson(route('admin.devices.check-registration', [
            'reg' => 'HA0267',
            'raw' => 'B2WN0262020HA0267'
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'exists' => true,
            'id' => $device->id,
            'registration_number' => 'HA0267',
            'is_in_warehouse' => true,
        ]);

        // Cek langsung dengan query reg = B2WN0262020HA0267 (ekstraksi otomatis backend)
        $response2 = $this->actingAs($admin)->getJson(route('admin.devices.check-registration', [
            'reg' => 'B2WN0262020HA0267'
        ]));

        $response2->assertStatus(200);
        $response2->assertJson([
            'exists' => true,
            'id' => $device->id,
            'registration_number' => 'HA0267',
            'is_in_warehouse' => true,
        ]);
    }

    public function test_stock_query_matches_lintasarta_full_barcode()
    {
        $admin = User::where('role', 'admin')->first();

        Device::create([
            'registration_number' => 'HA0267',
            'serial_number' => 'B2WN0262020HA0267',
            'name' => 'Fortigate 50E Lintasarta',
            'brand' => 'Fortinet',
            'type' => 'Router',
            'condition' => 'baru',
            'stock_status' => 'tersedia_hb_dikta',
            'current_location' => 'Gudang HB Dikta',
        ]);

        // Cari stok HB Dikta dengan barcode fisik lengkap
        $response = $this->actingAs($admin)->get(route('admin.devices.stock', [
            'search' => 'B2WN0262020HA0267'
        ]));

        $response->assertStatus(200);
        $response->assertSee('HA0267');
        $response->assertSee('Fortigate 50E Lintasarta');
    }

    public function test_create_device_extracts_suffix_from_lintasarta_barcode()
    {
        $admin = User::where('role', 'admin')->first();

        // Masuk form pendaftaran perangkat baru membawa parameter barcode fisik lengkap
        $response = $this->actingAs($admin)->get(route('admin.devices.create', [
            'reg' => 'B2WN0262020HA0267'
        ]));

        $response->assertStatus(200);
        // Suggested reg harus terekstrak menjadi HA0267
        $response->assertSee('HA0267');
    }
}
