<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMinecraftSrvToSubdomainsTable extends Migration
{
    public function up(): void
    {
        Schema::table('subdomains', function (Blueprint $table) {
            $table->string('cloudflare_srv_record_id')->nullable()->after('cloudflare_record_id');
            $table->unsignedInteger('srv_port')->nullable()->after('cloudflare_srv_record_id');
        });
    }

    public function down(): void
    {
        Schema::table('subdomains', function (Blueprint $table) {
            $table->dropColumn(['cloudflare_srv_record_id', 'srv_port']);
        });
    }
}
