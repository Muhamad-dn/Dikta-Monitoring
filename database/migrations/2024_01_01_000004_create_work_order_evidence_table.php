<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('work_order_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignId('technician_id')->constrained('users')->cascadeOnDelete();
            $table->enum('photo_type', [
                'pengambilan_pt', 
                'tiba_lokasi', 
                'hasil_instalasi', 
                'redaman_ont', 
                'kendala', 
                'lainnya'
            ])->default('hasil_instalasi');
            $table->string('photo_path');
            $table->string('caption')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('work_order_evidence');
    }
};
