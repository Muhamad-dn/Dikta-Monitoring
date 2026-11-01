<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Device;
use App\Models\DeviceHistory;
use App\Models\User;

class StockHbDiktaDeviceSeeder extends Seeder
{
    public function run()
    {
        $admin = User::where('role', 'admin')->first();

        $items = [
            ['nama_perangkat' => 'Fortinet 200F', 'registrasi' => 'B2WN0286541MA0001', 'area' => 'BWA'],
            ['nama_perangkat' => 'Fortinet 200F', 'registrasi' => 'SN: FG200FT920908060', 'area' => 'BWA'],
            ['nama_perangkat' => 'RC', 'registrasi' => 'B2WN01AH020402300', 'area' => 'BWA'],
            ['nama_perangkat' => 'RC', 'registrasi' => 'B2WN01AH020401813', 'area' => 'BWA'],
            ['nama_perangkat' => 'RC', 'registrasi' => 'B2WN01AH020401365', 'area' => 'BWA'],
            ['nama_perangkat' => 'RC', 'registrasi' => 'B2WN01AH020401914', 'area' => 'BWA'],
            ['nama_perangkat' => 'ONT RAISEECOM', 'registrasi' => 'B2WN01000462A0171', 'area' => 'BWA'],
            ['nama_perangkat' => 'RAX', 'registrasi' => 'B2WN0286404MA0343', 'area' => 'BWA'],
            ['nama_perangkat' => 'RAX', 'registrasi' => 'B2WN0286404MA0563', 'area' => 'BWA'],
            ['nama_perangkat' => 'Automatic Transfer Switch', 'registrasi' => 'B2WN0100041I00036', 'area' => 'BWA'],
            ['nama_perangkat' => 'Cisco 4431', 'registrasi' => 'B2WN0131048PA0012', 'area' => 'BWA'],
            ['nama_perangkat' => 'Mini pc', 'registrasi' => 'B1SV0100004V00024', 'area' => 'BWA'],
            ['nama_perangkat' => 'Poe Cambium', 'registrasi' => 'B2WS0391048MA0844', 'area' => 'BWA'],
            ['nama_perangkat' => 'Cambium', 'registrasi' => 'Registr tidak terlihat', 'area' => 'BWA'],
            ['nama_perangkat' => 'Comtech Satellite Modem', 'registrasi' => 'SN: 152667998', 'area' => 'BWA'],
            ['nama_perangkat' => 'RC', 'registrasi' => 'B2WN01AH020401861', 'area' => 'BWA'],
            ['nama_perangkat' => 'RC', 'registrasi' => 'B2WN01AH020401607', 'area' => 'BWA'],
            ['nama_perangkat' => 'UPS', 'registrasi' => 'B3CD0100213MA0010', 'area' => 'BWA'],
            ['nama_perangkat' => 'RC', 'registrasi' => 'B2WN01AH020400899', 'area' => 'BWA'],
            ['nama_perangkat' => 'Cisco 2900', 'registrasi' => 'B2WN0131042M00248', 'area' => 'BWA'],
            ['nama_perangkat' => 'Cisco 2900', 'registrasi' => 'B2WN0131042M00349', 'area' => 'BWA'],
            ['nama_perangkat' => 'RC', 'registrasi' => 'B2WN01AH020401975', 'area' => 'BWA'],
            ['nama_perangkat' => 'Cisco', 'registrasi' => 'B2WN0231042U00098', 'area' => 'BWA'],
            ['nama_perangkat' => 'Cisco 1900 series', 'registrasi' => 'B2WN0131042L00724', 'area' => 'BWA'],
            ['nama_perangkat' => 'RC', 'registrasi' => 'B2WN01AH020401908', 'area' => 'BWA'],
            ['nama_perangkat' => 'RC', 'registrasi' => 'B2WN01AH020400855', 'area' => 'BWA'],
            ['nama_perangkat' => 'UPS', 'registrasi' => 'B3CD0100213MA0004', 'area' => 'BWA'],
            ['nama_perangkat' => 'Rack', 'registrasi' => 'B3RK012N010S00202', 'area' => 'BWA'],
        ];

        foreach ($items as $item) {
            $brand = explode(' ', $item['nama_perangkat'])[0];
            $type = 'Lainnya';
            if (stripos($item['nama_perangkat'], 'ONT') !== false) {
                $type = 'ONT';
            } elseif (stripos($item['nama_perangkat'], 'Cisco') !== false || stripos($item['nama_perangkat'], 'Fortinet') !== false) {
                $type = 'Router';
            } elseif (stripos($item['nama_perangkat'], 'Modem') !== false) {
                $type = 'Modem';
            } elseif (stripos($item['nama_perangkat'], 'Cambium') !== false) {
                $type = 'Access Point';
            }

            $dev = Device::updateOrCreate(
                ['registration_number' => $item['registrasi']],
                [
                    'serial_number' => $item['registrasi'],
                    'name' => $item['nama_perangkat'],
                    'brand' => $brand,
                    'type' => $type,
                    'area' => $item['area'],
                    'stock_status' => 'tersedia_pt',
                    'current_location' => 'Gudang HB Dikta',
                    'condition' => 'baru',
                    'activity' => 'Spare Dikta',
                    'notes' => 'Stok cadangan inventaris HB Dikta (Area ' . $item['area'] . ')',
                ]
            );

            if ($admin && $dev->wasRecentlyCreated) {
                DeviceHistory::create([
                    'device_id' => $dev->id,
                    'actor_id' => $admin->id,
                    'action' => 'Registrasi Stok HB Dikta',
                    'previous_status' => null,
                    'new_status' => 'tersedia_pt',
                    'from_location' => 'Data Awal Stok Gudang',
                    'to_location' => 'Gudang HB Dikta',
                    'notes' => 'Pendaftaran stok perangkat HB Dikta',
                ]);
            }
        }
    }
}
