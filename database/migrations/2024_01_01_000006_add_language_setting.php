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
            $table->string('language', 10)->default('en')->after('display_unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('freeservers_settings', function (Blueprint $table) {
            $table->dropColumn('language');
        });
    }
};
