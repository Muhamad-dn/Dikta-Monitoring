<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number')->unique()->index();
            $table->string('serial_number')->unique()->index();
            $table->string('name');
            $table->string('brand');
            $table->string('model')->nullable();
            $table->string('type')->default('ONT'); // ONT, Router, Switch, Access Point, Modem, Lainnya
            $table->enum('stock_status', [
                'tersedia_pt', 
                'tersedia_hb_dikta', 
                'dibawa_teknisi', 
                'terpasang_pelanggan', 
                'terinstal', 
                'pending', 
                'rusak', 
                'hilang', 
                'dismantle'
            ])->default('tersedia_pt')->index();
            $table->foreignId('current_technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('current_location')->default('Gudang PT DIKTA');
            $table->enum('condition', ['baru', 'bekas_bagus', 'rusak'])->default('baru');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('devices');
    }
};
