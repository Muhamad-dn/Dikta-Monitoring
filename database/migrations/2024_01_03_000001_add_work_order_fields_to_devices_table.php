<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('devices', function (Blueprint $table) {
            if (!Schema::hasColumn('devices', 'nojar')) {
                $table->string('nojar')->nullable()->after('model');
            }
            if (!Schema::hasColumn('devices', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('nojar');
            }
            if (!Schema::hasColumn('devices', 'address')) {
                $table->text('address')->nullable()->after('customer_name');
            }
            if (!Schema::hasColumn('devices', 'activity')) {
                $table->string('activity')->nullable()->after('address'); // Survey, PSB, Troubleshoot, Simulasi
            }
        });
    }

    public function down()
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn(['nojar', 'customer_name', 'address', 'activity']);
        });
    }
};
