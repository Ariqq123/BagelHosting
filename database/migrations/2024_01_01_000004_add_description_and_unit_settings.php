<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('freeservers_settings', function (Blueprint $table) {
            $table->string('server_description')->default('Free Server created via Free Servers Extension');
            $table->string('display_unit')->default('MB'); // 'MB' or 'GB'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('freeservers_settings', function (Blueprint $table) {
            $table->dropColumn(['server_description', 'display_unit']);
        });
    }
};
