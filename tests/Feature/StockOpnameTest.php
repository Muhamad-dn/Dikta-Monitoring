<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockOpnameTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $teknisi;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::where('role', 'admin')->first();
        $this->teknisi = User::where('role', 'teknisi')->first();
    }

    public function test_admin_can_access_stock_opname_index()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.stock-opname.index'));
        $response->assertStatus(200);
        $response->assertSee('Arsip Stock Opname');
    }

    public function test_teknisi_cannot_access_stock_opname()
    {
        $response = $this->actingAs($this->teknisi)->get(route('admin.stock-opname.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_quick_start_stock_opname()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.stock-opname.start'));
        $opname = StockOpname::first();
        $this->assertNotNull($opname);
        $response->assertRedirect(route('admin.stock-opname.show', $opname));
    }

    public function test_admin_can_create_new_stock_opname_session()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.stock-opname.store'), [
            'period_month' => 11,
            'period_year' => 2026,
            'location' => 'hb_jkt',
            'admin_name' => 'Budi Administrator',
        ]);

        $this->assertDatabaseHas('stock_opnames', [
            'period_month' => 11,
            'period_year' => 2026,
            'location' => 'hb_jkt',
            'admin_name' => 'Budi Administrator',
            'status' => 'in_progress',
        ]);

        $opname = StockOpname::first();
        $this->assertNotNull($opname);
        $this->assertGreaterThan(0, $opname->total_system);
        $this->assertCount($opname->total_system, $opname->items);

        $response->assertRedirect(route('admin.stock-opname.show', $opname));
    }

    public function test_admin_can_scan_device_successfully()
    {
        $this->actingAs($this->admin)->post(route('admin.stock-opname.store'), [
            'period_month' => 11,
            'period_year' => 2026,
            'location' => 'hb_jkt',
            'admin_name' => 'Budi Administrator',
        ]);

        $opname = StockOpname::first();
        $firstItem = $opname->items()->first();

        // Scan barcode
        $response = $this->actingAs($this->admin)->postJson(route('admin.stock-opname.scan', $opname), [
            'code' => $firstItem->registration_number,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'type' => 'matched',
        ]);

        $firstItem->refresh();
        $this->assertEquals('matched', $firstItem->physical_status);
        $this->assertNotNull($firstItem->scanned_at);

        $opname->refresh();
        $this->assertEquals(1, $opname->total_matched);
    }

    public function test_duplicate_scan_is_handled()
    {
        $this->actingAs($this->admin)->post(route('admin.stock-opname.store'), [
            'period_month' => 11,
            'period_year' => 2026,
            'location' => 'hb_jkt',
            'admin_name' => 'Budi Administrator',
        ]);

        $opname = StockOpname::first();
        $firstItem = $opname->items()->first();

        // Scan 1
        $this->actingAs($this->admin)->postJson(route('admin.stock-opname.scan', $opname), [
            'code' => $firstItem->registration_number,
        ]);

        // Scan 2 (duplicate)
        $response = $this->actingAs($this->admin)->postJson(route('admin.stock-opname.scan', $opname), [
            'code' => $firstItem->registration_number,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'already_scanned' => true,
        ]);
    }

    public function test_scanning_unregistered_code_returns_404()
    {
        $this->actingAs($this->admin)->post(route('admin.stock-opname.store'), [
            'period_month' => 11,
            'period_year' => 2026,
            'location' => 'hb_jkt',
            'admin_name' => 'Budi Administrator',
        ]);

        $opname = StockOpname::first();

        $response = $this->actingAs($this->admin)->postJson(route('admin.stock-opname.scan', $opname), [
            'code' => 'KODE-TIDAK-ADA-12345',
        ]);

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'not_found' => true,
        ]);
    }

    public function test_admin_can_complete_stock_opname()
    {
        $this->actingAs($this->admin)->post(route('admin.stock-opname.store'), [
            'period_month' => 11,
            'period_year' => 2026,
            'location' => 'hb_jkt',
            'admin_name' => 'Budi Administrator',
        ]);

        $opname = StockOpname::first();

        $response = $this->actingAs($this->admin)->post(route('admin.stock-opname.complete', $opname), [
            'notes' => 'Pemeriksaan selesai, semua unit lengkap.',
        ]);

        $opname->refresh();
        $this->assertEquals('completed', $opname->status);
        $this->assertNotNull($opname->completed_at);
        $this->assertEquals('Pemeriksaan selesai, semua unit lengkap.', $opname->notes);

        $response->assertRedirect(route('admin.stock-opname.index'));
    }

    public function test_admin_can_export_and_print_stock_opname()
    {
        $this->actingAs($this->admin)->post(route('admin.stock-opname.store'), [
            'period_month' => 11,
            'period_year' => 2026,
            'location' => 'hb_jkt',
            'admin_name' => 'Budi Administrator',
        ]);

        $opname = StockOpname::first();

        // Print view
        $printResponse = $this->actingAs($this->admin)->get(route('admin.stock-opname.print', $opname));
        $printResponse->assertStatus(200);
        $printResponse->assertSee('BERITA ACARA STOCK OPNAME');

        // Export Excel (.xlsx default)
        $exportResponse = $this->actingAs($this->admin)->get(route('admin.stock-opname.export', $opname));
        $exportResponse->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml', $exportResponse->headers->get('Content-Type'));

        // Verify XLSX is valid and parseable
        $tempXlsx = tempnam(sys_get_temp_dir(), 'test_exp_') . '.xlsx';
        file_put_contents($tempXlsx, $exportResponse->getContent());
        $this->assertFileExists($tempXlsx);
        $this->assertGreaterThan(1000, filesize($tempXlsx));
        $parsed = \App\Services\SimpleExcelService::parseXlsx($tempXlsx);
        $this->assertNotEmpty($parsed);
        @unlink($tempXlsx);

        // Export CSV (?format=csv)
        $csvResponse = $this->actingAs($this->admin)->get(route('admin.stock-opname.export', [$opname, 'format' => 'csv']));
        $csvResponse->assertStatus(200);
        $this->assertStringContainsString('text/csv', $csvResponse->headers->get('Content-Type'));
    }

    public function test_admin_can_include_external_device_into_stock_and_opname()
    {
        $this->actingAs($this->admin)->post(route('admin.stock-opname.store'), [
            'period_month' => 11,
            'period_year' => 2026,
            'location' => 'hb_jkt',
            'admin_name' => 'Budi Administrator',
        ]);

        $opname = StockOpname::first();

        // Buat perangkat dengan status di luar stok (misal: dibawa teknisi)
        $externalDevice = Device::create([
            'registration_number' => 'EXT-DEV-001',
            'name' => 'Mikrotik External Router',
            'serial_number' => 'SN-EXT-001',
            'brand' => 'Mikrotik',
            'stock_status' => 'dibawa_teknisi',
            'current_technician_id' => $this->teknisi->id,
            'current_location' => 'Lapangan',
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('admin.stock-opname.include-external', $opname), [
            'device_id' => $externalDevice->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $externalDevice->refresh();
        $this->assertEquals('tersedia_hb_jkt', $externalDevice->stock_status);
        $this->assertNull($externalDevice->current_technician_id);
        $this->assertEquals('Gudang HB Jakarta', $externalDevice->current_location);

        $this->assertDatabaseHas('device_histories', [
            'device_id' => $externalDevice->id,
            'action' => 'Masuk Stok via Stock Opname',
            'new_status' => 'tersedia_hb_jkt',
        ]);

        $this->assertDatabaseHas('stock_opname_items', [
            'stock_opname_id' => $opname->id,
            'device_id' => $externalDevice->id,
            'registration_number' => 'EXT-DEV-001',
            'physical_status' => 'matched',
        ]);
    }

    public function test_admin_can_scan_external_device_with_auto_include()
    {
        $this->actingAs($this->admin)->post(route('admin.stock-opname.store'), [
            'period_month' => 11,
            'period_year' => 2026,
            'location' => 'hb_jkt',
            'admin_name' => 'Budi Administrator',
        ]);

        $opname = StockOpname::first();

        $externalDevice = Device::create([
            'registration_number' => 'AUTO-SCAN-999',
            'name' => 'Cisco Switch Outdoor',
            'serial_number' => 'SN-AUTO-999',
            'brand' => 'Cisco',
            'stock_status' => 'rusak',
            'current_location' => 'Area Perbaikan',
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('admin.stock-opname.scan', $opname), [
            'code' => 'AUTO-SCAN-999',
            'auto_include' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'type' => 'matched',
            'auto_included' => true,
        ]);

        $externalDevice->refresh();
        $this->assertEquals('tersedia_hb_jkt', $externalDevice->stock_status);
        $this->assertEquals('Gudang HB Jakarta', $externalDevice->current_location);
    }

    public function test_admin_can_register_new_device_into_stock_and_opname()
    {
        $this->actingAs($this->admin)->post(route('admin.stock-opname.store'), [
            'period_month' => 11,
            'period_year' => 2026,
            'location' => 'hb_jkt',
            'admin_name' => 'Budi Administrator',
        ]);

        $opname = StockOpname::first();

        $response = $this->actingAs($this->admin)->postJson(route('admin.stock-opname.register-new', $opname), [
            'registration_number' => 'NEW-UNIT-888',
            'name' => 'Teltonika Dual SIM 4G Gateway',
            'serial_number' => 'SN-NEW-888',
            'brand' => 'Teltonika',
            'type' => 'Gateway',
            'area' => 'Rak Baru A1',
            'condition' => 'Baru',
            'notes' => 'Ditemukan fisik belum terdata',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $device = Device::where('registration_number', 'NEW-UNIT-888')->first();
        $this->assertNotNull($device);
        $this->assertEquals('tersedia_hb_jkt', $device->stock_status);
        $this->assertEquals('Gudang HB Jakarta', $device->current_location);

        $this->assertDatabaseHas('device_histories', [
            'device_id' => $device->id,
            'action' => 'Registrasi Perangkat Baru via Stock Opname',
            'new_status' => 'tersedia_hb_jkt',
        ]);

        $this->assertDatabaseHas('stock_opname_items', [
            'stock_opname_id' => $opname->id,
            'device_id' => $device->id,
            'registration_number' => 'NEW-UNIT-888',
            'physical_status' => 'matched',
        ]);
    }

    public function test_admin_can_submit_existing_external_device_through_so_form()
    {
        $this->actingAs($this->admin)->post(route('admin.stock-opname.store'), [
            'period_month' => 11,
            'period_year' => 2026,
            'location' => 'hb_jkt',
            'admin_name' => 'Budi Administrator',
        ]);

        $opname = StockOpname::first();

        // Buat perangkat yang sedang dibawa teknisi
        $existingDevice = Device::create([
            'registration_number' => 'EXIST-TECH-777',
            'name' => 'Existing Tech Router',
            'serial_number' => 'SN-TECH-777',
            'brand' => 'Mikrotik',
            'stock_status' => 'dibawa_teknisi',
            'current_technician_id' => $this->teknisi->id,
            'current_location' => 'Lapangan',
        ]);

        // Kirim lewat form SO (register-new) dengan warehouse_hub hb_ckr
        $response = $this->actingAs($this->admin)->postJson(route('admin.stock-opname.register-new', $opname), [
            'registration_number' => 'EXIST-TECH-777',
            'name' => 'Existing Tech Router Updated',
            'area' => 'BWA',
            'warehouse_hub' => 'hb_ckr',
            'notes' => 'Ditemukan fisik saat SO dan dipindahkan ke CKR',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $existingDevice->refresh();
        $this->assertEquals('tersedia_hb_ckr', $existingDevice->stock_status);
        $this->assertNull($existingDevice->current_technician_id);
        $this->assertEquals('Gudang HB Cikarang', $existingDevice->current_location);
        $this->assertEquals('BWA', $existingDevice->area);

        $this->assertDatabaseHas('stock_opname_items', [
            'stock_opname_id' => $opname->id,
            'device_id' => $existingDevice->id,
            'registration_number' => 'EXIST-TECH-777',
            'physical_status' => 'matched',
        ]);
    }

    public function test_admin_can_search_external_devices()
    {
        $this->actingAs($this->admin)->post(route('admin.stock-opname.store'), [
            'period_month' => 11,
            'period_year' => 2026,
            'location' => 'hb_jkt',
            'admin_name' => 'Budi Administrator',
        ]);

        $opname = StockOpname::first();

        Device::create([
            'registration_number' => 'SEARCH-TEST-123',
            'name' => 'Searchable Modem Router',
            'serial_number' => 'SN-SEARCH-TEST-123',
            'brand' => 'Huawei',
            'stock_status' => 'dibawa_teknisi',
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('admin.stock-opname.search-external', [
            $opname,
            'q' => 'SEARCH-TEST'
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertNotEmpty($response->json('devices'));
    }
}

