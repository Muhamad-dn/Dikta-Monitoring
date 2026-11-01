<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Device;
use App\Models\WorkOrder;
use App\Models\DeviceHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MonitoringTeknisiFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_login_page_is_accessible()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('PT DIKTA');
    }

    public function test_admin_can_login_and_access_dashboard()
    {
        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticated();

        $dash = $this->actingAs(User::where('username', 'admin')->first())->get('/admin/dashboard');
        $dash->assertStatus(200);
        $dash->assertSee('Dashboard Administrator');
    }

    public function test_coordinator_can_create_work_order_and_allocate_device()
    {
        $coordinator = User::where('role', 'koordinator')->first();
        $technician = User::where('username', 'budi')->first();
        $device = Device::where('stock_status', 'tersedia_pt')->first();

        $response = $this->actingAs($coordinator)->post('/coordinator/work-orders', [
            'nojar' => 'NJR999',
            'title' => 'Pemasangan Internet Rumah Bapak Hendra',
            'category' => 'Instalasi',
            'customer_name' => 'Bapak Hendra',
            'customer_phone' => '081234567999',
            'location_address' => 'Jl. Kenanga No. 88, Jakarta',
            'technician_id' => $technician->id,
            'device_id' => $device->id,
            'status_notes' => 'Pasang sebelum jam 12 siang',
        ]);

        $this->assertDatabaseHas('work_orders', [
            'nojar' => 'NJR999',
            'status' => 'belum_dikerjakan',
            'technician_id' => $technician->id,
            'device_id' => $device->id,
        ]);

        // Verify DeviceHistory created for allocation
        $this->assertDatabaseHas('device_histories', [
            'device_id' => $device->id,
            'action' => 'Penugasan oleh Koordinator',
        ]);
    }

    public function test_technician_scan_and_pickup_device_validation()
    {
        $technician = User::where('username', 'budi')->first();
        $task = WorkOrder::where('nojar', 'NJR001')->first();
        $device = $task->device;

        // 1. Mismatched scan should fail
        $failResponse = $this->actingAs($technician)
            ->from("/technician/tasks/{$task->id}/scan-pickup")
            ->post("/technician/tasks/{$task->id}/confirm-pickup", [
                'registration_number' => 'WRONG-CODE-999',
            ]);
        $failResponse->assertSessionHas('error');
        $this->assertEquals('belum_dikerjakan', $task->fresh()->status);

        // 2. Correct match scan should succeed
        $successResponse = $this->actingAs($technician)
            ->from("/technician/tasks/{$task->id}/scan-pickup")
            ->post("/technician/tasks/{$task->id}/confirm-pickup", [
                'registration_number' => $device->registration_number,
                'notes' => 'Unit diambil lengkap',
            ]);

        $successResponse->assertSessionHas('success');
        
        // Assert state changes
        $this->assertEquals('perangkat_diambil', $task->fresh()->status);
        $this->assertEquals('dibawa_teknisi', $device->fresh()->stock_status);
        $this->assertEquals($technician->id, $device->fresh()->current_technician_id);

        // Assert audit trail history
        $this->assertDatabaseHas('device_histories', [
            'device_id' => $device->id,
            'action' => 'Pengambilan Perangkat di Gudang PT',
            'new_status' => 'dibawa_teknisi',
            'technician_id' => $technician->id,
        ]);
    }

    public function test_technician_flow_to_completion()
    {
        $technician = User::where('username', 'budi')->first();
        $task = WorkOrder::where('nojar', 'NJR001')->first();
        $device = $task->device;

        // Pick up device
        $res1 = $this->actingAs($technician)
            ->from("/technician/tasks/{$task->id}/scan-pickup")
            ->post("/technician/tasks/{$task->id}/confirm-pickup", [
                'registration_number' => $device->registration_number,
            ]);
        $this->assertEquals('perangkat_diambil', $task->fresh()->status);

        // Start Journey
        $res2 = $this->actingAs($technician)
            ->from("/technician/tasks/{$task->id}")
            ->post("/technician/tasks/{$task->id}/flow", [
                'action' => 'start_journey',
            ]);
        $this->assertEquals('dalam_perjalanan', $task->fresh()->status);

        // Arrived at location
        $res3 = $this->actingAs($technician)
            ->from("/technician/tasks/{$task->id}")
            ->post("/technician/tasks/{$task->id}/flow", [
                'action' => 'arrived',
            ]);
        $this->assertEquals('sampai_lokasi', $task->fresh()->status);

        // Start work
        $res4 = $this->actingAs($technician)
            ->from("/technician/tasks/{$task->id}")
            ->post("/technician/tasks/{$task->id}/flow", [
                'action' => 'start_work',
            ]);
        $this->assertEquals('sedang_dikerjakan', $task->fresh()->status);

        // Submit completion with outcome 'terinstal'
        $res5 = $this->actingAs($technician)
            ->from("/technician/tasks/{$task->id}/complete")
            ->post("/technician/tasks/{$task->id}/submit-complete", [
                'status' => 'terinstal',
                'status_notes' => 'Pemasangan selesai dan internet menyala normal.',
            ]);

        $this->assertEquals('terinstal', $task->fresh()->status);
        $this->assertEquals('terpasang_pelanggan', $device->fresh()->stock_status);
        $this->assertNull($device->fresh()->current_technician_id);

        // Verify full audit trail has all lifecycle stages
        $histories = DeviceHistory::where('device_id', $device->id)->pluck('action')->toArray();
        $this->assertContains('Pengambilan Perangkat di Gudang PT', $histories);
        $this->assertContains('Menuju Alamat Lokasi Pekerjaan', $histories);
        $this->assertContains('Tiba di Alamat Lokasi Pekerjaan', $histories);
        $this->assertContains('Mulai Pengerjaan Lapangan', $histories);
        $this->assertContains('Terinstal di Pelanggan', $histories);
    }
}
