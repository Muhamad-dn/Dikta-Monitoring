<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use App\Models\Device;
use App\Services\SimpleExcelService;
use Illuminate\Http\UploadedFile;

class DeviceExcelImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_can_download_excel_and_csv_template()
    {
        $admin = User::where('role', 'admin')->first();

        // Download default XLSX
        $responseXlsx = $this->actingAs($admin)->get(route('admin.devices.import-template', ['format' => 'xlsx']));
        $responseXlsx->assertStatus(200);
        $responseXlsx->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertNotEmpty($responseXlsx->getContent());

        // Download CSV with hub hb_jkt
        $responseCsvJkt = $this->actingAs($admin)->get(route('admin.devices.import-template', ['format' => 'csv', 'hub' => 'hb_jkt']));
        $responseCsvJkt->assertStatus(200);
        $responseCsvJkt->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Nomor Registrasi', $responseCsvJkt->getContent());
        $this->assertStringContainsString('Tersedia di HB JKT', $responseCsvJkt->getContent());

        // Download CSV with hub hb_ckr
        $responseCsvCkr = $this->actingAs($admin)->get(route('admin.devices.import-template', ['format' => 'csv', 'hub' => 'hb_ckr']));
        $responseCsvCkr->assertStatus(200);
        $this->assertStringContainsString('Tersedia di HB CKR', $responseCsvCkr->getContent());
    }

    public function test_import_devices_target_warehouse_hb_jkt_and_hb_ckr()
    {
        $admin = User::where('role', 'admin')->first();

        // 1. Import to HB JKT
        $csvJkt = "Nomor Registrasi,Serial Number,Nama Perangkat,Merk,Model,Tipe,Area,NOJAR,Nama Pelanggan,Alamat,Aktivitas,Status Stok,Kondisi,Catatan\n";
        $csvJkt .= "JKT-001,SN-JKT-001,Router JKT,MikroTik,RB750,Router,BWA,,,,,baru,Unit masuk Jakarta\n";

        $tmpJkt = tempnam(sys_get_temp_dir(), 'test_jkt_') . '.csv';
        file_put_contents($tmpJkt, $csvJkt);

        $uploadJkt = new UploadedFile($tmpJkt, 'import_jkt.csv', 'text/csv', null, true);

        $resJkt = $this->actingAs($admin)->post(route('admin.devices.import'), [
            'file' => $uploadJkt,
            'warehouse_hub' => 'hb_jkt',
        ]);
        $resJkt->assertRedirect();
        $resJkt->assertSessionHas('success');

        $this->assertDatabaseHas('devices', [
            'registration_number' => 'JKT-001',
            'stock_status' => 'tersedia_hb_jkt',
            'current_location' => 'Gudang HB JKT (Stok Spare)',
        ]);
        @unlink($tmpJkt);

        // 2. Import to HB CKR
        $csvCkr = "Nomor Registrasi,Serial Number,Nama Perangkat,Merk,Model,Tipe,Area,NOJAR,Nama Pelanggan,Alamat,Aktivitas,Status Stok,Kondisi,Catatan\n";
        $csvCkr .= "CKR-001,SN-CKR-001,Router CKR,MikroTik,RB750,Router,BDA,,,,,baru,Unit masuk Cikarang\n";

        $tmpCkr = tempnam(sys_get_temp_dir(), 'test_ckr_') . '.csv';
        file_put_contents($tmpCkr, $csvCkr);

        $uploadCkr = new UploadedFile($tmpCkr, 'import_ckr.csv', 'text/csv', null, true);

        $resCkr = $this->actingAs($admin)->post(route('admin.devices.import'), [
            'file' => $uploadCkr,
            'warehouse_hub' => 'hb_ckr',
        ]);
        $resCkr->assertRedirect();
        $resCkr->assertSessionHas('success');

        $this->assertDatabaseHas('devices', [
            'registration_number' => 'CKR-001',
            'stock_status' => 'tersedia_hb_ckr',
            'current_location' => 'Gudang HB CKR (Stok Spare)',
        ]);
        @unlink($tmpCkr);
    }

    public function test_import_devices_from_csv_file()
    {
        $admin = User::where('role', 'admin')->first();

        $csvContent = "Nomor Registrasi,Serial Number,Nama Perangkat,Merk,Model,Tipe,Area,NOJAR,Nama Pelanggan,Alamat,Aktivitas,Status Stok,Kondisi,Catatan\n";
        $csvContent .= "HA9901,SN-HUAWEI-9901,HUAWEI HG8245H5,Huawei,HG8245H5,ONT,BWA,NJR9901,PT Maju Jaya,Jl. Merdeka No. 1,PSB,Tersedia di HB Dikta,baru,Stok baru\n";
        $csvContent .= "HA9902,SN-ZTE-9902,ZTE F609,ZTE,F609,ONT,BDA,,,,,Tersedia di HB Dikta,baru,Spare unit\n";

        $tmpFile = tempnam(sys_get_temp_dir(), 'test_import_') . '.csv';
        file_put_contents($tmpFile, $csvContent);

        $uploadedFile = new UploadedFile(
            $tmpFile,
            'import_test.csv',
            'text/csv',
            null,
            true
        );

        $response = $this->actingAs($admin)->post(route('admin.devices.import'), [
            'file' => $uploadedFile,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('devices', [
            'registration_number' => 'HA9901',
            'serial_number' => 'SN-HUAWEI-9901',
            'brand' => 'Huawei',
            'customer_name' => 'PT Maju Jaya',
            'nojar' => 'NJR9901',
        ]);

        $this->assertDatabaseHas('devices', [
            'registration_number' => 'HA9902',
            'serial_number' => 'SN-ZTE-9902',
            'brand' => 'ZTE',
            'stock_status' => 'tersedia_hb_jkt',
        ]);

        @unlink($tmpFile);
    }

    public function test_import_devices_from_xlsx_file_with_lintasarta_barcode_auto_extraction()
    {
        $admin = User::where('role', 'admin')->first();

        // Template generated by SimpleExcelService contains sample with HA0267 and B2WN0262020HA0267
        $xlsxBinary = SimpleExcelService::generateSampleXlsx();

        $tmpFile = tempnam(sys_get_temp_dir(), 'test_sample_') . '.xlsx';
        file_put_contents($tmpFile, $xlsxBinary);

        $uploadedFile = new UploadedFile(
            $tmpFile,
            'sample_devices.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->actingAs($admin)->post(route('admin.devices.import'), [
            'file' => $uploadedFile,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('devices', [
            'registration_number' => 'HA0267',
            'serial_number' => 'B2WN0262020HA0267',
        ]);

        $this->assertDatabaseHas('devices', [
            'registration_number' => 'HA2068',
            'serial_number' => 'B2WN0262020HA2068',
        ]);

        $this->assertDatabaseHas('devices', [
            'registration_number' => 'MK0109',
            'serial_number' => 'RB951-SN998877',
            'brand' => 'MikroTik',
            'type' => 'Router',
        ]);

        @unlink($tmpFile);
    }

    public function test_import_auto_extracts_suffix_when_full_barcode_in_registration_column()
    {
        $admin = User::where('role', 'admin')->first();

        // Barcode Lintasarta B2WN0262020HA5555 placed in registration column, serial number empty
        $csvContent = "Nomor Registrasi,Serial Number,Nama Perangkat,Merk,Model,Tipe,Area,NOJAR,Nama Pelanggan,Alamat,Aktivitas,Status Stok,Kondisi,Catatan\n";
        $csvContent .= "B2WN0262020HA5555,,FORTIGATE 60F,PT DIKTA,FG-60F,ONT,BWA,,,,Spare Dikta,Tersedia di HB Dikta,baru,Auto extract test\n";

        $tmpFile = tempnam(sys_get_temp_dir(), 'test_extract_') . '.csv';
        file_put_contents($tmpFile, $csvContent);

        $uploadedFile = new UploadedFile(
            $tmpFile,
            'extract_test.csv',
            'text/csv',
            null,
            true
        );

        $response = $this->actingAs($admin)->post(route('admin.devices.import'), [
            'file' => $uploadedFile,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // registration_number should be extracted as HA5555, while serial_number stores the full physical barcode B2WN0262020HA5555!
        $this->assertDatabaseHas('devices', [
            'registration_number' => 'HA5555',
            'serial_number' => 'B2WN0262020HA5555',
            'name' => 'FORTIGATE 60F',
        ]);

        @unlink($tmpFile);
    }

    public function test_device_index_and_stock_pages_render_import_button_and_modal()
    {
        $admin = User::where('role', 'admin')->first();

        // 1. Device Index Page
        $resIndex = $this->actingAs($admin)->get(route('admin.devices.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Import Excel HB JKT');
        $resIndex->assertSee('Import Excel HB CKR');
        $resIndex->assertSee('importExcelModal');
        $resIndex->assertSee(route('admin.devices.import-template', ['format' => 'xlsx', 'hub' => 'hb_jkt']));

        // 2. Device Stock Page
        $resStock = $this->actingAs($admin)->get(route('admin.devices.stock'));
        $resStock->assertStatus(200);
        $resStock->assertSee('Import Excel HB JKT');
        $resStock->assertSee('Import Excel HB CKR');
        $resStock->assertSee('importExcelModal');
        $resStock->assertSee(route('admin.devices.import-template', ['format' => 'xlsx', 'hub' => 'hb_jkt']));
    }
}
