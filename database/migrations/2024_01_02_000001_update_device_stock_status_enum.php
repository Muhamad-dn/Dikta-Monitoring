<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE devices MODIFY COLUMN stock_status ENUM('tersedia_pt', 'tersedia_hb_dikta', 'dibawa_teknisi', 'terpasang_pelanggan', 'terinstal', 'pending', 'rusak', 'hilang', 'dismantle') NOT NULL DEFAULT 'tersedia_pt'");
        } elseif (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
            DB::statement('
                CREATE TABLE IF NOT EXISTS "devices_temp" (
                    "id" integer not null primary key autoincrement, 
                    "registration_number" varchar not null, 
                    "serial_number" varchar not null, 
                    "name" varchar not null, 
                    "brand" varchar not null, 
                    "model" varchar, 
                    "type" varchar not null default \'ONT\', 
                    "stock_status" varchar check ("stock_status" in (\'tersedia_pt\', \'tersedia_hb_dikta\', \'dibawa_teknisi\', \'terpasang_pelanggan\', \'terinstal\', \'pending\', \'rusak\', \'hilang\', \'dismantle\')) not null default \'tersedia_pt\', 
                    "current_technician_id" integer, 
                    "current_location" varchar not null default \'Gudang HB Dikta\', 
                    "condition" varchar check ("condition" in (\'baru\', \'bekas_bagus\', \'rusak\')) not null default \'baru\', 
                    "notes" text, 
                    "created_at" datetime, 
                    "updated_at" datetime, 
                    foreign key("current_technician_id") references "users"("id") on delete set null
                );
            ');
            DB::statement('INSERT INTO "devices_temp" SELECT * FROM "devices";');
            DB::statement('DROP TABLE "devices";');
            DB::statement('ALTER TABLE "devices_temp" RENAME TO "devices";');
            DB::statement('CREATE UNIQUE INDEX "devices_registration_number_unique" ON "devices"("registration_number");');
            DB::statement('CREATE UNIQUE INDEX "devices_serial_number_unique" ON "devices"("serial_number");');
            DB::statement('CREATE INDEX "devices_stock_status_index" ON "devices"("stock_status");');
            DB::statement('PRAGMA foreign_keys = ON;');
        }
    }

    public function down()
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE devices MODIFY COLUMN stock_status ENUM('tersedia_pt', 'dibawa_teknisi', 'terpasang_pelanggan', 'rusak', 'dismantle') NOT NULL DEFAULT 'tersedia_pt'");
        }
    }
};
