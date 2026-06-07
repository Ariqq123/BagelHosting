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
        Schema::create('freeservers_allowed_eggs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('egg_id');
            $table->string('custom_name')->nullable(); // Optional: Custom Name für das Egg
            $table->text('custom_description')->nullable(); // Optional: Custom Description
            $table->integer('custom_memory')->nullable(); // Optional: Override Memory
            $table->integer('custom_disk')->nullable(); // Optional: Override Disk
            $table->integer('custom_cpu')->nullable(); // Optional: Override CPU
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->foreign('egg_id')->references('id')->on('eggs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('freeservers_allowed_eggs');
    }
};
