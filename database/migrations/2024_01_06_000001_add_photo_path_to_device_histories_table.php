<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('device_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('device_histories', 'photo_path')) {
                $table->string('photo_path')->nullable()->after('notes');
            }
        });
    }

    public function down()
    {
        Schema::table('device_histories', function (Blueprint $table) {
            if (Schema::hasColumn('device_histories', 'photo_path')) {
                $table->dropColumn('photo_path');
            }
        });
    }
};
