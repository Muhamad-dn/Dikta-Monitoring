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

        // Export CSV
        $exportResponse = $this->actingAs($this->admin)->get(route('admin.stock-opname.export', $opname));
        $exportResponse->assertStatus(200);
        $this->assertStringContainsString('text/csv', $exportResponse->headers->get('Content-Type'));
    }
}

