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
    public function up(): void
    {
        if (!Schema::hasTable('admin_notifications')) {
            Schema::create('admin_notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('device_id')->nullable()->constrained('devices')->nullOnDelete();
                $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('type', 50)->default('device_added_stock');
                $table->string('title');
                $table->text('message')->nullable();
                $table->string('registration_number', 100)->nullable()->index();
                $table->string('device_name', 150)->nullable();
                $table->string('hub', 30)->nullable()->index(); // hb_jkt, hb_ckr
                $table->string('source', 100)->nullable(); // Lintasarta, etc.
                $table->string('target_url', 500)->nullable();
                $table->boolean('is_read')->default(false)->index();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_notifications');
    }
};
