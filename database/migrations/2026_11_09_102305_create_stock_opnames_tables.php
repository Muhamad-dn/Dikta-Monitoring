<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->string('opname_code')->unique();
            $table->string('title');
            $table->unsignedTinyInteger('period_month');
            $table->unsignedSmallInteger('period_year');
            $table->string('location')->default('semua');
            $table->string('status')->default('in_progress'); // in_progress, completed, cancelled
            $table->integer('total_system')->default(0);
            $table->integer('total_physical')->default(0);
            $table->integer('total_matched')->default(0);
            $table->integer('total_missing')->default(0);
            $table->integer('total_surplus')->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('conducted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_opname_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained('stock_opnames')->cascadeOnDelete();
            $table->foreignId('device_id')->nullable()->constrained('devices')->nullOnDelete();
            $table->string('registration_number');
            $table->string('device_name')->nullable();
            $table->string('area')->nullable();
            $table->string('system_status')->nullable();
            $table->string('system_location')->nullable();
            $table->string('physical_status')->default('missing'); // matched, missing, surplus_unregistered, wrong_location
            $table->timestamp('scanned_at')->nullable();
            $table->foreignId('scanned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['stock_opname_id', 'registration_number']);
            $table->index(['stock_opname_id', 'physical_status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_opname_items');
        Schema::dropIfExists('stock_opnames');
    }
};
