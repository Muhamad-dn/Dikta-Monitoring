<?php

namespace Tests\Feature;

use App\Models\AdminNotification;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNotificationStockTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $technician;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Administrator Sistem',
            'username' => 'admin_test',
            'email' => 'admin@dikta.co.id',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->technician = User::factory()->create([
            'name' => 'Aldi Saputra',
            'username' => 'aldi_test',
            'email' => 'aldi@dikta.co.id',
            'role' => 'teknisi',
            'status' => 'active',
        ]);
    }

    public function test_technician_adding_device_from_lintasarta_to_hb_jkt_creates_notification()
    {
        $response = $this->actingAs($this->technician)->post(route('technician.devices.store'), [
            'registration_number' => 'B2WS0200137MA0089',
            'kode_spek' => 'B2WS0200137MA',
            'name' => 'Bivocom Industrial 4G Router',
            'area' => 'CDA',
            'activity' => 'Spare Dikta',
            'warehouse_hub' => 'hb_jkt',
            'source_origin' => 'Lintasarta (Gudang Tekno / Drop Point)',
            'notes' => 'Diambil dari Lintasarta untuk stok cadangan Jakarta',
        ]);

        $response->assertRedirect(route('technician.dashboard'));

        $device = Device::where('registration_number', 'B2WS0200137MA0089')->first();
        $this->assertNotNull($device);
        $this->assertEquals('tersedia_hb_jkt', $device->stock_status);
        $this->assertStringContainsString('Gudang HB JKT', $device->current_location);

        // Periksa notifikasi admin telah dibuat
        $notification = AdminNotification::where('device_id', $device->id)->first();
        $this->assertNotNull($notification);
        $this->assertEquals('hb_jkt', $notification->hub);
        $this->assertEquals('Aldi Saputra', $notification->technician->name);
        $this->assertFalse($notification->is_read);
        $this->assertStringContainsString('HB JKT', $notification->title);
        $this->assertStringContainsString('B2WS0200137MA0089', $notification->message);
        $this->assertStringContainsString('Lintasarta', $notification->message);
    }

    public function test_technician_adding_device_from_lintasarta_to_hb_ckr_creates_notification()
    {
        $response = $this->actingAs($this->technician)->post(route('technician.devices.store'), [
            'registration_number' => 'B2WN0262020HA9999',
            'name' => 'Huawei HG8245H5 ONT',
            'area' => 'BWA',
            'activity' => 'Spare Dikta',
            'warehouse_hub' => 'hb_ckr',
            'source_origin' => 'Lintasarta (Gudang Tekno / Drop Point)',
        ]);

        $response->assertRedirect(route('technician.dashboard'));

        $device = Device::where('registration_number', 'B2WN0262020HA9999')->first();
        $this->assertNotNull($device);
        $this->assertEquals('tersedia_hb_ckr', $device->stock_status);
        $this->assertStringContainsString('Gudang HB CKR', $device->current_location);

        $notification = AdminNotification::where('device_id', $device->id)->first();
        $this->assertNotNull($notification);
        $this->assertEquals('hb_ckr', $notification->hub);
        $this->assertStringContainsString('HB CKR', $notification->title);
        $this->assertFalse($notification->is_read);
    }

    public function test_admin_clicking_notification_marks_as_read_and_redirects_to_stock_device()
    {
        $device = Device::create([
            'registration_number' => 'B2WS0200137MA0089',
            'serial_number' => 'SN-B2WS-0089',
            'name' => 'Bivocom Industrial 4G Router',
            'brand' => 'Bivocom',
            'model' => 'Bivocom Router',
            'type' => 'ONT',
            'area' => 'CDA',
            'stock_status' => 'tersedia_hb_jkt',
            'current_location' => 'Gudang HB JKT (Stok Spare)',
            'condition' => 'baru',
        ]);

        $notification = AdminNotification::create([
            'device_id' => $device->id,
            'technician_id' => $this->technician->id,
            'actor_id' => $this->technician->id,
            'type' => 'device_added_stock',
            'title' => 'Perangkat Baru Ditambahkan ke Stok HB JKT',
            'message' => "Teknisi Aldi Saputra mengambil {$device->name} dari Lintasarta",
            'registration_number' => $device->registration_number,
            'device_name' => $device->name,
            'hub' => 'hb_jkt',
            'source' => 'Lintasarta',
            'target_url' => route('admin.devices.stock', [
                'hub' => 'hb_jkt',
                'search' => $device->registration_number,
                'highlight' => $device->id,
            ]),
            'is_read' => false,
        ]);

        $this->assertFalse($notification->is_read);

        // Akses route "go"
        $response = $this->actingAs($this->admin)->get(route('admin.notifications.go', $notification));

        // Mengarahkan langsung ke data perangkat di stok gudang HB JKT
        $response->assertRedirect(route('admin.devices.stock', [
            'hub' => 'hb_jkt',
            'search' => 'B2WS0200137MA0089',
            'highlight' => $device->id,
        ]));

        $this->assertTrue($notification->fresh()->is_read);
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_admin_notification_poll_and_mark_all_read()
    {
        AdminNotification::create([
            'technician_id' => $this->technician->id,
            'title' => 'Perangkat 1',
            'message' => 'Pesan 1',
            'registration_number' => 'REG-1',
            'hub' => 'hb_jkt',
            'is_read' => false,
        ]);

        AdminNotification::create([
            'technician_id' => $this->technician->id,
            'title' => 'Perangkat 2',
            'message' => 'Pesan 2',
            'registration_number' => 'REG-2',
            'hub' => 'hb_ckr',
            'is_read' => false,
        ]);

        // Cek poll API
        $response = $this->actingAs($this->admin)->getJson(route('admin.notifications.poll'));
        $response->assertOk();
        $response->assertJson([
            'unread_count' => 2,
        ]);

        // Tandai semua dibaca
        $markResponse = $this->actingAs($this->admin)->postJson(route('admin.notifications.mark-all-read'));
        $markResponse->assertOk();
        $markResponse->assertJson(['success' => true]);

        // Poll lagi, unread count harus 0
        $response2 = $this->actingAs($this->admin)->getJson(route('admin.notifications.poll'));
        $response2->assertOk();
        $response2->assertJson([
            'unread_count' => 0,
        ]);
    }

    public function test_stock_page_displays_notification_highlight_banner()
    {
        $device = Device::create([
            'registration_number' => 'B2WS0200137MA0089',
            'serial_number' => 'SN-B2WS-0089',
            'name' => 'Bivocom Industrial 4G Router',
            'brand' => 'Bivocom',
            'model' => 'Bivocom Router',
            'type' => 'ONT',
            'area' => 'CDA',
            'stock_status' => 'tersedia_hb_jkt',
            'current_location' => 'Gudang HB JKT (Stok Spare)',
            'condition' => 'baru',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.devices.stock', [
            'hub' => 'hb_jkt',
            'search' => 'B2WS0200137MA0089',
            'highlight' => $device->id,
        ]));

        $response->assertOk();
        $response->assertSee('Unit dari Notifikasi Teknisi:');
        $response->assertSee('B2WS0200137MA0089');
        $response->assertSee('Bivocom Industrial 4G Router');
        $response->assertSee('Unit Baru Ditambahkan');
    }
}
