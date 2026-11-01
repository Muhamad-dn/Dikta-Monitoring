<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('device_specifications')) {
            Schema::create('device_specifications', function (Blueprint $table) {
                $table->id();
                $table->string('spec_code')->unique()->index();
                $table->string('name');
                $table->string('brand')->nullable();
                $table->string('type')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('devices', function (Blueprint $table) {
            if (!Schema::hasColumn('devices', 'kode_spek')) {
                $table->string('kode_spek')->nullable()->after('registration_number')->index();
            }
        });
    }

    public function down()
    {
        Schema::table('devices', function (Blueprint $table) {
            if (Schema::hasColumn('devices', 'kode_spek')) {
                $table->dropColumn('kode_spek');
            }
        });

        Schema::dropIfExists('device_specifications');
    }
};
