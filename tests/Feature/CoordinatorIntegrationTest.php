<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;

class CoordinatorIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_wulan_can_login_with_wulan_and_dikta123()
    {
        $response = $this->post(route('login.post'), [
            'username' => 'wulan',
            'password' => 'dikta123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('wulan', auth()->user()->username);
        $this->assertEquals('koordinator', auth()->user()->role);
    }

    public function test_wulan_can_login_with_dt_prefix_dtwulan()
    {
        $response = $this->post(route('login.post'), [
            'username' => 'DTwulan',
            'password' => 'dikta123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('wulan', auth()->user()->username);
    }

    public function test_coordinator_dashboard_is_same_as_admin_except_technician_management()
    {
        $wulan = User::where('username', 'wulan')->first();
        $this->assertNotNull($wulan);

        $response = $this->actingAs($wulan)->get(route('admin.dashboard'));
        $response->assertStatus(200);

        // Memiliki menu dan metrik utama yang sama seperti Admin
        $response->assertSee('Dashboard Utama');
        $response->assertSee('Data Perangkat');
        $response->assertSee('Stok Gudang HB');
        $response->assertSee('Stock Opname');
        $response->assertSee('Riwayat Perpindahan');

        // Menu "Kelola Teknisi" dihilangkan untuk Koordinator
        $response->assertDontSee('Kelola Teknisi');

        // Badge peran menampilkan KOORDINATOR
        $response->assertSee('KOORDINATOR');
        $response->assertSee('Wulan');
    }

    public function test_coordinator_can_access_devices_stock_so_history()
    {
        $wulan = User::where('username', 'wulan')->first();

        // Data Perangkat
        $this->actingAs($wulan)->get(route('admin.devices.index'))->assertStatus(200);

        // Stok Gudang HB
        $this->actingAs($wulan)->get(route('admin.devices.stock'))->assertStatus(200);

        // Stock Opname
        $this->actingAs($wulan)->get(route('admin.stock-opname.index'))->assertStatus(200);

        // Riwayat Perpindahan
        $this->actingAs($wulan)->get(route('admin.history.index'))->assertStatus(200);
    }

    public function test_coordinator_cannot_access_kelola_teknisi()
    {
        $wulan = User::where('username', 'wulan')->first();

        // Akses langsung ke kelola teknisi harus ditolak (403 Forbidden)
        $response = $this->actingAs($wulan)->get(route('admin.technicians.index'));
        $response->assertStatus(403);
    }

    public function test_admin_still_has_kelola_teknisi_access()
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Kelola Teknisi');

        $techRes = $this->actingAs($admin)->get(route('admin.technicians.index'));
        $techRes->assertStatus(200);
    }
}
