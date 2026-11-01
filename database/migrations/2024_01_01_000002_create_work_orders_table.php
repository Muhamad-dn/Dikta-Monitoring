<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('nojar')->unique()->index();
            $table->string('title');
            $table->enum('category', [
                'Survei', 
                'Instalasi', 
                'Aktivasi', 
                'Troubleshooting', 
                'Dismantle'
            ])->default('Instalasi');
            $table->string('customer_name');
            $table->string('customer_phone')->nullable();
            $table->text('location_address');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->foreignId('coordinator_id')->constrained('users');
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('device_id')->nullable()->constrained('devices')->nullOnDelete();
            $table->enum('status', [
                'belum_dikerjakan',
                'perangkat_diambil',
                'dalam_perjalanan',
                'sampai_lokasi',
                'sedang_dikerjakan',
                'terinstal',
                'pending',
                'gagal_install',
                'selesai'
            ])->default('belum_dikerjakan')->index();
            $table->text('status_notes')->nullable();
            $table->timestamp('pickup_time')->nullable();
            $table->timestamp('arrival_time')->nullable();
            $table->timestamp('completion_time')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('work_orders');
    }
};
