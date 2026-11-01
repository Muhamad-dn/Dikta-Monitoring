<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Device;
use App\Models\WorkOrder;
use App\Models\DeviceHistory;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 1. Akun Pengguna
        $admin = User::create([
            'name' => 'Administrator Sistem',
            'username' => 'admin',
            'email' => 'admin@ptdikta.co.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '081234567001',
            'status' => 'active',
        ]);

        $koordinator = User::create([
            'name' => 'Hendra Setiawan (Koordinator)',
            'username' => 'koordinator',
            'email' => 'koordinator@ptdikta.co.id',
            'password' => Hash::make('password'),
            'role' => 'koordinator',
            'phone' => '081234567002',
            'status' => 'active',
        ]);

        $teknisiBudi = User::create([
            'name' => 'Budi Santoso',
            'username' => 'budi',
            'email' => 'budi@ptdikta.co.id',
            'password' => Hash::make('password'),
            'role' => 'teknisi',
            'phone' => '081234567003',
            'status' => 'active',
        ]);

        $teknisiAgus = User::create([
            'name' => 'Agus Pratama',
            'username' => 'agus',
            'email' => 'agus@ptdikta.co.id',
            'password' => Hash::make('password'),
            'role' => 'teknisi',
            'phone' => '081234567004',
            'status' => 'active',
        ]);

        $teknisiRian = User::create([
            'name' => 'Rian Hidayat',
            'username' => 'rian',
            'email' => 'rian@ptdikta.co.id',
            'password' => Hash::make('password'),
            'role' => 'teknisi',
            'phone' => '081234567005',
            'status' => 'active',
        ]);

        // 2. Data Master Perangkat & Stok
        $reg001 = Device::create([
            'registration_number' => 'REG-001',
            'serial_number' => 'HW-2026-001',
            'name' => 'ONT Huawei HG8245H5',
            'brand' => 'Huawei',
            'model' => 'HG8245H5 GPON',
            'type' => 'ONT',
            'stock_status' => 'tersedia_pt',
            'current_technician_id' => null,
            'current_location' => 'Gudang PT DIKTA (Rak A-01)',
            'condition' => 'baru',
            'notes' => 'Siap untuk penugasan instalasi baru',
        ]);

        $reg002 = Device::create([
            'registration_number' => 'REG-002',
            'serial_number' => 'FH-2026-002',
            'name' => 'ONT Fiberhome AN5506-04',
            'brand' => 'Fiberhome',
            'model' => 'AN5506-04-F',
            'type' => 'ONT',
            'stock_status' => 'dibawa_teknisi',
            'current_technician_id' => $teknisiAgus->id,
            'current_location' => 'Dibawa Teknisi Agus Pratama (Menuju Lapangan)',
            'condition' => 'baru',
            'notes' => 'Diambil teknisi untuk aktivasi NOJAR NJR002',
        ]);

        $reg003 = Device::create([
            'registration_number' => 'REG-003',
            'serial_number' => 'ZT-2026-003',
            'name' => 'ONT ZTE F609 V3',
            'brand' => 'ZTE',
            'model' => 'ZXHN F609',
            'type' => 'ONT',
            'stock_status' => 'terpasang_pelanggan',
            'current_technician_id' => null,
            'current_location' => 'Jl. Pahlawan No. 88 (Pelanggan PT DIKTA)',
            'condition' => 'baru',
            'notes' => 'Instalasi selesai dan aktif',
        ]);

        $reg004 = Device::create([
            'registration_number' => 'REG-004',
            'serial_number' => 'MK-2026-004',
            'name' => 'Router MikroTik RB750Gr3',
            'brand' => 'MikroTik',
            'model' => 'hEX Gigabit',
            'type' => 'Router',
            'stock_status' => 'dibawa_teknisi',
            'current_technician_id' => $teknisiRian->id,
            'current_location' => 'Jl. Melati No. 12 (Lokasi Pelanggan)',
            'condition' => 'baru',
            'notes' => 'Sedang pengerjaan troubleshooting',
        ]);

        $reg005 = Device::create([
            'registration_number' => 'REG-005',
            'serial_number' => 'HW-2026-005',
            'name' => 'ONT Huawei EG8145V5',
            'brand' => 'Huawei',
            'model' => 'EchoLife EG8145V5',
            'type' => 'ONT',
            'stock_status' => 'dismantle',
            'current_technician_id' => null,
            'current_location' => 'Gudang PT DIKTA (Area Dismantle)',
            'condition' => 'bekas_bagus',
            'notes' => 'Hasil bongkaran pelanggan putus langganan',
        ]);

        $reg006 = Device::create([
            'registration_number' => 'REG-006',
            'serial_number' => 'TL-2026-006',
            'name' => 'Access Point TP-Link EAP225',
            'brand' => 'TP-Link',
            'model' => 'Omada AC1350',
            'type' => 'Access Point',
            'stock_status' => 'tersedia_pt',
            'current_technician_id' => null,
            'current_location' => 'Gudang PT DIKTA (Rak B-02)',
            'condition' => 'baru',
            'notes' => 'Stok cadangan AP korporat',
        ]);

        $reg007 = Device::create([
            'registration_number' => 'REG-007',
            'serial_number' => 'ZT-2026-007',
            'name' => 'ONT ZTE F670L Dual Band',
            'brand' => 'ZTE',
            'model' => 'ZXHN F670L AC1200',
            'type' => 'ONT',
            'stock_status' => 'tersedia_pt',
            'current_technician_id' => null,
            'current_location' => 'Gudang PT DIKTA (Rak A-02)',
            'condition' => 'baru',
            'notes' => 'Siap digunakan',
        ]);

        // 3. Catat Riwayat Masuk Stok Awal
        foreach ([$reg001, $reg002, $reg003, $reg004, $reg005, $reg006, $reg007] as $dev) {
            DeviceHistory::create([
                'device_id' => $dev->id,
                'actor_id' => $admin->id,
                'action' => 'Registrasi & Stok Masuk PT',
                'previous_status' => null,
                'new_status' => 'tersedia_pt',
                'from_location' => 'Distributor / Vendor',
                'to_location' => 'Gudang PT DIKTA',
                'notes' => 'Perangkat didaftarkan ke sistem dan ditempatkan di gudang PT DIKTA',
                'created_at' => now()->subDays(5),
            ]);
        }

        // 4. Data Pekerjaan (Work Orders)
        // NJR001: Ditugaskan kepada Budi, Siap Diambil di PT
        $wo1 = WorkOrder::create([
            'nojar' => 'NJR001',
            'title' => 'Pemasangan Baru Internet Fiber 50 Mbps',
            'category' => 'Instalasi',
            'customer_name' => 'Bapak Suryono',
            'customer_phone' => '081399881122',
            'location_address' => 'Jl. Contoh No. 10, RT 03/RW 05, Kel. Sukamaju',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'coordinator_id' => $koordinator->id,
            'technician_id' => $teknisiBudi->id,
            'device_id' => $reg001->id,
            'status' => 'belum_dikerjakan',
            'status_notes' => 'Koordinator telah menugaskan Budi. Teknisi perlu scan REG-001 di PT sebelum berangkat.',
        ]);

        DeviceHistory::create([
            'device_id' => $reg001->id,
            'work_order_id' => $wo1->id,
            'technician_id' => $teknisiBudi->id,
            'actor_id' => $koordinator->id,
            'action' => 'Penugasan oleh Koordinator',
            'previous_status' => 'tersedia_pt',
            'new_status' => 'tersedia_pt',
            'from_location' => 'Gudang PT DIKTA',
            'to_location' => 'Gudang PT DIKTA (Alokasi: ' . $teknisiBudi->name . ')',
            'notes' => 'Perangkat ditetapkan untuk tiket NOJAR NJR001. Menunggu pengambilan fisik oleh teknisi.',
            'created_at' => now()->subHours(2),
        ]);

        // NJR002: Perangkat sudah diambil di PT oleh Agus
        $wo2 = WorkOrder::create([
            'nojar' => 'NJR002',
            'title' => 'Aktivasi Port Pelanggan Baru Paket Bisnis',
            'category' => 'Aktivasi',
            'customer_name' => 'Ibu Rahmawati',
            'customer_phone' => '081288776655',
            'location_address' => 'Ruko Grand City Blok B-4, Jl. Ahmad Yani',
            'coordinator_id' => $koordinator->id,
            'technician_id' => $teknisiAgus->id,
            'device_id' => $reg002->id,
            'status' => 'perangkat_diambil',
            'status_notes' => 'Perangkat telah divalidasi scan di gudang PT. Teknisi sedang bersiap menuju lokasi.',
            'pickup_time' => now()->subMinutes(30),
        ]);

        DeviceHistory::create([
            'device_id' => $reg002->id,
            'work_order_id' => $wo2->id,
            'technician_id' => $teknisiAgus->id,
            'actor_id' => $teknisiAgus->id,
            'action' => 'Pengambilan Perangkat di Gudang PT',
            'previous_status' => 'tersedia_pt',
            'new_status' => 'dibawa_teknisi',
            'from_location' => 'Gudang PT DIKTA',
            'to_location' => 'Dibawa Teknisi Agus Pratama',
            'notes' => 'Perangkat berhasil di-scan dan divalidasi di PT. Tanggung jawab berpindah ke teknisi.',
            'created_at' => now()->subMinutes(30),
        ]);

        // NJR003: Sedang dikerjakan oleh Rian di lokasi
        $wo3 = WorkOrder::create([
            'nojar' => 'NJR003',
            'title' => 'Penggantian Router Rusak / Sering RTO',
            'category' => 'Troubleshooting',
            'customer_name' => 'CV. Mitra Abadi',
            'customer_phone' => '081122334455',
            'location_address' => 'Jl. Melati No. 12, Kompleks Perkantoran Hijau',
            'coordinator_id' => $koordinator->id,
            'technician_id' => $teknisiRian->id,
            'device_id' => $reg004->id,
            'status' => 'sedang_dikerjakan',
            'status_notes' => 'Teknisi sudah tiba di lokasi dan sedang melakukan instalasi konfigurasi MikroTik.',
            'pickup_time' => now()->subHours(1)->subMinutes(30),
            'arrival_time' => now()->subMinutes(40),
        ]);

        DeviceHistory::create([
            'device_id' => $reg004->id,
            'work_order_id' => $wo3->id,
            'technician_id' => $teknisiRian->id,
            'actor_id' => $teknisiRian->id,
            'action' => 'Pengambilan Perangkat di Gudang PT',
            'previous_status' => 'tersedia_pt',
            'new_status' => 'dibawa_teknisi',
            'from_location' => 'Gudang PT DIKTA',
            'to_location' => 'Dibawa Teknisi Rian Hidayat',
            'notes' => 'Validasi barcode sukses di gudang PT DIKTA',
            'created_at' => now()->subHours(1)->subMinutes(30),
        ]);

        DeviceHistory::create([
            'device_id' => $reg004->id,
            'work_order_id' => $wo3->id,
            'technician_id' => $teknisiRian->id,
            'actor_id' => $teknisiRian->id,
            'action' => 'Tiba di Lokasi Pelanggan',
            'previous_status' => 'dibawa_teknisi',
            'new_status' => 'dibawa_teknisi',
            'from_location' => 'Dalam Perjalanan',
            'to_location' => 'Jl. Melati No. 12 (CV. Mitra Abadi)',
            'notes' => 'Teknisi sampai di lokasi dan mulai pengecekan jaringan',
            'created_at' => now()->subMinutes(40),
        ]);

        // NJR004: Pekerjaan selesai
        $wo4 = WorkOrder::create([
            'nojar' => 'NJR004',
            'title' => 'Instalasi ONT Baru Rumah Tinggal',
            'category' => 'Instalasi',
            'customer_name' => 'Bapak Gunawan',
            'customer_phone' => '081233445566',
            'location_address' => 'Jl. Pahlawan No. 88, RT 01/RW 02',
            'coordinator_id' => $koordinator->id,
            'technician_id' => $teknisiBudi->id,
            'device_id' => $reg003->id,
            'status' => 'selesai',
            'status_notes' => 'Instalasi selesai dengan baik. Redaman -19.5 dBm, internet online stabil.',
            'pickup_time' => now()->subDays(1)->subHours(4),
            'arrival_time' => now()->subDays(1)->subHours(3),
            'completion_time' => now()->subDays(1)->subHours(1),
        ]);

        DeviceHistory::create([
            'device_id' => $reg003->id,
            'work_order_id' => $wo4->id,
            'technician_id' => $teknisiBudi->id,
            'actor_id' => $teknisiBudi->id,
            'action' => 'Pengambilan di PT',
            'previous_status' => 'tersedia_pt',
            'new_status' => 'dibawa_teknisi',
            'from_location' => 'Gudang PT DIKTA',
            'to_location' => 'Dibawa Teknisi Budi Santoso',
            'notes' => 'Diambil oleh teknisi Budi',
            'created_at' => now()->subDays(1)->subHours(4),
        ]);

        DeviceHistory::create([
            'device_id' => $reg003->id,
            'work_order_id' => $wo4->id,
            'technician_id' => $teknisiBudi->id,
            'actor_id' => $teknisiBudi->id,
            'action' => 'Terinstal di Pelanggan',
            'previous_status' => 'dibawa_teknisi',
            'new_status' => 'terpasang_pelanggan',
            'from_location' => 'Dibawa Teknisi Budi Santoso',
            'to_location' => 'Jl. Pahlawan No. 88 (Pelanggan Bapak Gunawan)',
            'notes' => 'Perangkat telah terpasang permanen di rumah pelanggan dan aktif',
            'created_at' => now()->subDays(1)->subHours(1),
        ]);

        // 7. Pendaftaran 28 Perangkat Stok HB Dikta Area BWA
        $this->call(StockHbDiktaDeviceSeeder::class);
    }
}
