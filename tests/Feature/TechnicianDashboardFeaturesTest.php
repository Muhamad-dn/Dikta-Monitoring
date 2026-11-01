<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Device;
use App\Models\WorkOrder;
use App\Models\DeviceHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TechnicianDashboardFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_technician_dashboard_renders_all_required_columns_and_sections()
    {
        $technician = User::where('role', 'teknisi')->first();

        // Assign a device to this technician
        $device = Device::create([
            'registration_number' => 'REG-TECH-001',
            'serial_number' => 'SN-TECH-001',
            'name' => 'Fortinet 200F',
            'brand' => 'Fortinet',
            'model' => 'Fortinet 200F',
            'type' => 'Router',
            'area' => 'BWA',
            'nojar' => 'NJR-888',
            'customer_name' => 'PT Pelanggan Maju',
            'address' => 'Jl. Jendral Sudirman Kav. 1',
            'activity' => 'PSB',
            'stock_status' => 'dibawa_teknisi',
            'current_technician_id' => $technician->id,
            'current_location' => 'Dibawa Teknisi ' . $technician->name,
            'condition' => 'baru',
        ]);

        $response = $this->actingAs($technician)->get(route('technician.dashboard'));
        $response->assertStatus(200);

        // Header and registration button
        $response->assertSee('Daftarkan Perangkat');
        $response->assertSee('Perangkat Saya');
        $response->assertSee('Update Status Lapangan');
        $response->assertSee('Riwayat Lapangan');

        // Columns in Perangkat Saya table
        $response->assertSee('NAMA PERANGKAT');
        $response->assertSee('NO REGISTRASI');
        $response->assertSee('NOJAR');
        $response->assertSee('PELANGGAN');

        // Row content
        $response->assertSee('REG-TECH-001');
        $response->assertSee('Fortinet 200F');
        $response->assertSee('NJR-888');
        $response->assertSee('PT Pelanggan Maju');

        // Actions
        $response->assertSee('Handover');
        $response->assertSee('Kembalikan');
        $response->assertSee('Update Status');

        // 8 Status options
        $response->assertSee('SAMPAI LOKASI');
        $response->assertSee('TERINSTAL');
        $response->assertSee('PENDING');
        $response->assertSee('GAGAL INSTAL');
        $response->assertSee('DIBAWA KEMBALI KE HB DIKTA');
        $response->assertSee('DI BAWA KEMBALI KE AREA');
        $response->assertSee('RUSAK');
        $response->assertSee('HILANG');
        $response->assertSee('Simpan Update');
    }

    public function test_technician_can_register_device()
    {
        $technician = User::where('role', 'teknisi')->first();

        // 1. Visit create page
        $createPage = $this->actingAs($technician)->get(route('technician.devices.create'));
        $createPage->assertStatus(200);
        $createPage->assertSee('Form Pendaftaran Perangkat');

        // 2. Submit new device
        $response = $this->actingAs($technician)->post(route('technician.devices.store'), [
            'registration_number' => 'B2WN0286541MA9999',
            'serial_number' => 'SN-FG200F-9999',
            'name' => 'Fortinet 200F Special',
            'area' => 'BWA',
            'activity' => 'PSB',
            'nojar' => 'NJR-9999',
            'customer_name' => 'Bank Mandiri Cabang',
            'address' => 'Jl. MH Thamrin No. 5',
            'technician_id' => $technician->id,
            'notes' => 'Pendaftaran dari teknisi lapangan',
        ]);

        $response->assertRedirect(route('technician.dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('devices', [
            'registration_number' => 'B2WN0286541MA9999',
            'name' => 'Fortinet 200F Special',
            'area' => 'BWA',
            'stock_status' => 'dibawa_teknisi',
            'current_technician_id' => $technician->id,
            'nojar' => 'NJR-9999',
            'customer_name' => 'Bank Mandiri Cabang',
        ]);

        $this->assertDatabaseHas('device_histories', [
            'action' => 'Pendaftaran Perangkat Baru oleh Teknisi',
            'technician_id' => $technician->id,
        ]);
    }

    public function test_technician_can_handover_device_to_another_technician()
    {
        $tech1 = User::where('role', 'teknisi')->first();
        $tech2 = User::where('role', 'teknisi')->where('id', '!=', $tech1->id)->first();

        $device = Device::create([
            'registration_number' => 'REG-HANDOVER-01',
            'serial_number' => 'SN-HANDOVER-01',
            'name' => 'Cisco 2900',
            'brand' => 'Cisco',
            'model' => '2900',
            'type' => 'Router',
            'area' => 'BWA',
            'stock_status' => 'dibawa_teknisi',
            'current_technician_id' => $tech1->id,
            'current_location' => 'Dibawa Teknisi ' . $tech1->name,
            'condition' => 'baru',
        ]);

        $response = $this->actingAs($tech1)->post(route('technician.devices.handover', $device), [
            'to_technician_id' => $tech2->id,
            'notes' => 'Oper tugas shift malam ke teknisi ' . $tech2->name,
        ]);

        $response->assertSessionHas('success');

        $device->refresh();
        $this->assertEquals($tech2->id, $device->current_technician_id);
        $this->assertStringContainsString($tech2->name, $device->current_location);

        $this->assertDatabaseHas('device_histories', [
            'device_id' => $device->id,
            'action' => 'Handover ke Teknisi Lain',
            'technician_id' => $tech2->id,
            'actor_id' => $tech1->id,
        ]);
    }

    public function test_technician_can_return_device_with_admin_like_modal()
    {
        $technician = User::where('role', 'teknisi')->first();

        // 1. Return to Gudang HB Dikta
        $dev1 = Device::create([
            'registration_number' => 'REG-RET-DIKTA',
            'serial_number' => 'SN-RET-01',
            'name' => 'RC Dikta',
            'brand' => 'RC',
            'model' => 'RC',
            'type' => 'ONT',
            'stock_status' => 'dibawa_teknisi',
            'current_technician_id' => $technician->id,
            'current_location' => 'Dibawa Teknisi ' . $technician->name,
            'condition' => 'bekas_bagus',
        ]);

        $res1 = $this->actingAs($technician)->post(route('technician.devices.return', $dev1), [
            'destination' => 'Gudang HB Dikta',
            'condition' => 'bekas_bagus',
            'return_notes' => 'Selesai tugas, barang dikembalikan utuh',
        ]);

        $res1->assertSessionHas('success');
        $dev1->refresh();
        $this->assertEquals('tersedia_pt', $dev1->stock_status);
        $this->assertNull($dev1->current_technician_id);
        $this->assertEquals('Gudang HB Dikta', $dev1->current_location);

        // 2. Return to Lintasarta (e.g. Gudang Tekno)
        $dev2 = Device::create([
            'registration_number' => 'REG-RET-LINTASARTA',
            'serial_number' => 'SN-RET-02',
            'name' => 'ONT Raisecom',
            'brand' => 'Raisecom',
            'model' => 'Raisecom',
            'type' => 'ONT',
            'stock_status' => 'dibawa_teknisi',
            'current_technician_id' => $technician->id,
            'current_location' => 'Dibawa Teknisi ' . $technician->name,
            'condition' => 'bekas_bagus',
        ]);

        $res2 = $this->actingAs($technician)->post(route('technician.devices.return', $dev2), [
            'destination' => 'Gudang Tekno',
            'condition' => 'bekas_bagus',
            'return_notes' => 'Dikembalikan ke Lintasarta Gudang Tekno',
        ]);

        $res2->assertSessionHas('success');
        $dev2->refresh();
        $this->assertEquals('Gudang Tekno', $dev2->returned_to_lintasarta);
        $this->assertNull($dev2->current_technician_id);
        $this->assertStringContainsString('Gudang Tekno', $dev2->current_location);
    }

    public function test_technician_can_update_field_status_with_photo_and_history()
    {
        Storage::fake('public');

        $technician = User::where('role', 'teknisi')->first();

        $device = Device::create([
            'registration_number' => 'REG-FIELD-01',
            'serial_number' => 'SN-FIELD-01',
            'name' => 'ONT Huawei',
            'brand' => 'Huawei',
            'model' => 'HG8245A',
            'type' => 'ONT',
            'area' => 'BWA',
            'stock_status' => 'dibawa_teknisi',
            'current_technician_id' => $technician->id,
            'current_location' => 'Dibawa Teknisi ' . $technician->name,
            'condition' => 'baru',
        ]);

        $fakePhoto = UploadedFile::fake()->image('bukti_instalasi.jpg', 600, 400);

        $response = $this->actingAs($technician)->post(route('technician.devices.update-status'), [
            'device_id' => $device->id,
            'status' => 'TERINSTAL',
            'nojar' => 'NJR-5544',
            'current_location' => 'Rumah Pelanggan Ibu Ani - Jl. Cempaka No. 9',
            'notes' => 'Pemasangan selesai dan lampu PON hijau stabil.',
            'evidence_photo' => $fakePhoto,
        ]);

        $response->assertSessionHas('success');

        $device->refresh();
        $this->assertEquals('terinstal', $device->stock_status);
        $this->assertNull($device->current_technician_id);
        $this->assertEquals('NJR-5544', $device->nojar);
        $this->assertStringContainsString('Ibu Ani', $device->current_location);

        // Verify DeviceHistory created with photo_path
        $history = DeviceHistory::where('device_id', $device->id)->latest()->first();
        $this->assertNotNull($history);
        $this->assertEquals('Perangkat Terinstal', $history->action);
        $this->assertNotNull($history->photo_path);
        $this->assertStringContainsString('evidence/NJR-5544', $history->photo_path);
        Storage::disk('public')->assertExists($history->photo_path);
    }

    public function test_technician_history_page_lists_activities()
    {
        $technician = User::where('role', 'teknisi')->first();

        $device = Device::create([
            'registration_number' => 'REG-HIST-01',
            'serial_number' => 'SN-HIST-01',
            'name' => 'Cisco Switch 2960',
            'brand' => 'Cisco',
            'model' => '2960',
            'type' => 'Switch',
            'area' => 'BWA',
            'stock_status' => 'tersedia_pt',
            'current_location' => 'Gudang HB Dikta',
            'condition' => 'baru',
        ]);

        DeviceHistory::create([
            'device_id' => $device->id,
            'technician_id' => $technician->id,
            'actor_id' => $technician->id,
            'action' => 'Sampai di Lokasi',
            'new_status' => 'dibawa_teknisi',
            'to_location' => 'Gedung Cyber 2 Lantai 15',
            'notes' => 'Teknisi tiba tepat waktu',
        ]);

        $response = $this->actingAs($technician)->get(route('technician.history.index'));
        $response->assertStatus(200);
        $response->assertSee('Riwayat Lapangan');
        $response->assertSee('REG-HIST-01');
        $response->assertSee('Sampai di Lokasi');
        $response->assertSee('Gedung Cyber 2 Lantai 15');
    }
}
