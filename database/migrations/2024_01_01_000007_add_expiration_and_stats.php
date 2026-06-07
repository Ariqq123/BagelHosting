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
        // Add expiration columns to settings (safe: skip if already exists)
        Schema::table('freeservers_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('freeservers_settings', 'expiration_days')) {
                $table->integer('expiration_days')->default(0)->after('language');
            }
            if (!Schema::hasColumn('freeservers_settings', 'extension_days')) {
                $table->integer('extension_days')->default(30)->after('expiration_days');
            }
            if (!Schema::hasColumn('freeservers_settings', 'allow_extension')) {
                $table->boolean('allow_extension')->default(true)->after('extension_days');
            }
            if (!Schema::hasColumn('freeservers_settings', 'enable_stats')) {
                $table->boolean('enable_stats')->default(true)->after('allow_extension');
            }
            if (!Schema::hasColumn('freeservers_settings', 'discord_webhook_url')) {
                $table->string('discord_webhook_url', 500)->nullable()->after('enable_stats');
            }
            if (!Schema::hasColumn('freeservers_settings', 'discord_notify_create')) {
                $table->boolean('discord_notify_create')->default(false)->after('discord_webhook_url');
            }
            if (!Schema::hasColumn('freeservers_settings', 'discord_notify_delete')) {
                $table->boolean('discord_notify_delete')->default(false)->after('discord_notify_create');
            }
            if (!Schema::hasColumn('freeservers_settings', 'discord_notify_expire')) {
                $table->boolean('discord_notify_expire')->default(false)->after('discord_notify_delete');
            }
        });

        // Add expiration tracking to servers (safe: skip if already exists)
        Schema::table('freeservers_servers', function (Blueprint $table) {
            if (!Schema::hasColumn('freeservers_servers', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('updated_at');
            }
            if (!Schema::hasColumn('freeservers_servers', 'last_extended_at')) {
                $table->timestamp('last_extended_at')->nullable()->after('expires_at');
            }
            if (!Schema::hasColumn('freeservers_servers', 'extension_count')) {
                $table->integer('extension_count')->default(0)->after('last_extended_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('freeservers_settings', function (Blueprint $table) {
            $table->dropColumn([
                'expiration_days',
                'extension_days',
                'allow_extension',
                'enable_stats',
                'discord_webhook_url',
                'discord_notify_create',
                'discord_notify_delete',
                'discord_notify_expire',
            ]);
        });

        Schema::table('freeservers_servers', function (Blueprint $table) {
            $table->dropColumn(['expires_at', 'last_extended_at', 'extension_count']);
        });
    }
};
