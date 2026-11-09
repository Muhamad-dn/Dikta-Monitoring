<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use App\Models\Device;

class DeviceSearchSuggestionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_search_suggestions_requires_authenticated_admin()
    {
        $response = $this->getJson(route('admin.devices.search-suggestions', ['q' => 'B']));
        $response->assertStatus(401);
    }

    public function test_search_suggestions_returns_empty_when_query_is_blank()
    {
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->getJson(route('admin.devices.search-suggestions', ['q' => ' ']));
        
        $response->assertStatus(200);
        $response->assertJson([]);
    }

    public function test_search_suggestions_triggers_with_1_character_and_matches_registration_number()
    {
        $admin = User::where('role', 'admin')->first();

        $device = Device::create([
            'registration_number' => 'B2WS0200137MA0999',
            'serial_number' => 'SN-B2WS-0999',
            'name' => 'Bivocom Industrial 4G Router',
            'brand' => 'Bivocom',
            'type' => 'Router',
            'area' => 'BWA',
            'nojar' => 'NJR999',
            'stock_status' => 'tersedia_hb_jkt',
            'current_location' => 'Gudang HB JKT',
            'condition' => 'baru',
        ]);

        // Cek hanya dengan 1 huruf saja: "B"
        $response = $this->actingAs($admin)->getJson(route('admin.devices.search-suggestions', ['q' => 'B']));
        
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertNotEmpty($data, 'Pencarian 1 karakter harus langsung memunculkan beberapa pilihan');
        
        $regNumbers = array_column($data, 'registration_number');
        $this->assertContains('B2WS0200137MA0999', $regNumbers);
    }

    public function test_search_suggestions_matches_nojar()
    {
        $admin = User::where('role', 'admin')->first();

        Device::create([
            'registration_number' => 'REG-NOJAR-TEST-777',
            'serial_number' => 'SN-NOJAR-777',
            'name' => 'Cisco Catalyst Switch 2960',
            'brand' => 'Cisco',
            'type' => 'Switch',
            'area' => 'BDA',
            'nojar' => 'NJR-KOTA-777',
            'stock_status' => 'tersedia_hb_jkt',
            'current_location' => 'Gudang HB JKT',
            'condition' => 'baru',
        ]);

        $response = $this->actingAs($admin)->getJson(route('admin.devices.search-suggestions', ['q' => 'NJR-KOTA']));
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertNotEmpty($data);
        $this->assertEquals('REG-NOJAR-TEST-777', $data[0]['registration_number']);
        $this->assertEquals('NJR-KOTA-777', $data[0]['nojar']);
    }

    public function test_search_suggestions_matches_device_name()
    {
        $admin = User::where('role', 'admin')->first();

        Device::create([
            'registration_number' => 'REG-NAME-555',
            'serial_number' => 'SN-NAME-555',
            'name' => 'MikroTik Cloud Core Router CCR2004',
            'brand' => 'Mikrotik',
            'type' => 'Router',
            'area' => 'SAST',
            'nojar' => 'NJR555',
            'stock_status' => 'tersedia_hb_jkt',
            'current_location' => 'Gudang HB JKT',
            'condition' => 'baru',
        ]);

        $response = $this->actingAs($admin)->getJson(route('admin.devices.search-suggestions', ['q' => 'MikroTik']));
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertNotEmpty($data);
        $names = array_column($data, 'name');
        $this->assertContains('MikroTik Cloud Core Router CCR2004', $names);
    }

    public function test_search_suggestions_matches_area()
    {
        $admin = User::where('role', 'admin')->first();

        Device::create([
            'registration_number' => 'REG-AREA-SEOA-111',
            'serial_number' => 'SN-AREA-111',
            'name' => 'Huawei ONT Dual Band HG8245',
            'brand' => 'Huawei',
            'type' => 'ONT',
            'area' => 'SEOA',
            'nojar' => 'NJR-SEOA-111',
            'stock_status' => 'tersedia_hb_jkt',
            'current_location' => 'Gudang HB JKT',
            'condition' => 'baru',
        ]);

        $response = $this->actingAs($admin)->getJson(route('admin.devices.search-suggestions', ['q' => 'SEOA']));
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertNotEmpty($data);
        $areas = array_column($data, 'area');
        $this->assertContains('SEOA', $areas);
    }

    public function test_admin_device_index_search_filters_by_area_and_nojar()
    {
        $admin = User::where('role', 'admin')->first();

        $devArea = Device::create([
            'registration_number' => 'REG-AREA-UNIQUE-99',
            'serial_number' => 'SN-AREA-UNIQUE-99',
            'name' => 'Device Area Special',
            'brand' => 'BrandTest',
            'type' => 'Router',
            'area' => 'NEA',
            'nojar' => 'NJR-SPECIAL-99',
            'stock_status' => 'tersedia_hb_jkt',
            'current_location' => 'Gudang HB JKT',
            'condition' => 'baru',
        ]);

        // Cari berdasarkan Area di index
        $resArea = $this->actingAs($admin)->get(route('admin.devices.index', ['search' => 'NEA']));
        $resArea->assertStatus(200);
        $resArea->assertSee('REG-AREA-UNIQUE-99');
        $resArea->assertSee('Device Area Special');

        // Cari berdasarkan NOJAR di index
        $resNojar = $this->actingAs($admin)->get(route('admin.devices.index', ['search' => 'NJR-SPECIAL-99']));
        $resNojar->assertStatus(200);
        $resNojar->assertSee('REG-AREA-UNIQUE-99');
    }

    public function test_admin_device_index_renders_autocomplete_search_elements()
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get(route('admin.devices.index'));

        $response->assertStatus(200);
        $response->assertSee('Cari berdasarkan Nomor Registrasi, NOJAR, Nama Perangkat, atau Area');
        $response->assertSee('placeholder="Cari No. Registrasi, NOJAR, Nama Perangkat, atau Area..."', false);
        $response->assertSee('deviceSearchAutocomplete');
        $response->assertSee('pilihan perangkat ditemukan');
    }
}

