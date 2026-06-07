<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('freeservers_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(false);
            $table->integer('max_servers_per_user')->default(1);
            $table->integer('default_memory')->default(1024); // MB
            $table->integer('default_disk')->default(5120); // MB
            $table->integer('default_cpu')->default(100); // %
            $table->integer('default_swap')->default(0); // MB
            $table->integer('default_io')->default(500);
            $table->integer('default_databases')->default(0);
            $table->integer('default_allocations')->default(1);
            $table->integer('default_backups')->default(0);
            $table->json('allowed_nodes')->nullable(); // Array von Node IDs
            $table->timestamps();
        });

        // Insert default settings
        DB::table('freeservers_settings')->insert([
            'enabled' => false,
            'max_servers_per_user' => 1,
            'default_memory' => 1024,
            'default_disk' => 5120,
            'default_cpu' => 100,
            'default_swap' => 0,
            'default_io' => 500,
            'default_databases' => 0,
            'default_allocations' => 1,
            'default_backups' => 0,
            'allowed_nodes' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('freeservers_settings');
    }
};
