<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('devices', function (Blueprint $table) {
            if (!Schema::hasColumn('devices', 'returned_to_lintasarta')) {
                $table->string('returned_to_lintasarta')->nullable()->after('notes');
            }
        });
    }

    public function down()
    {
        Schema::table('devices', function (Blueprint $table) {
            if (Schema::hasColumn('devices', 'returned_to_lintasarta')) {
                $table->dropColumn('returned_to_lintasarta');
            }
        });
    }
};
