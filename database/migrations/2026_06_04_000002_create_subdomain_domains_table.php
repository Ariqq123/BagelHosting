<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubdomainDomainsTable extends Migration
{
    public function up(): void
    {
        Schema::create('subdomain_domains', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('cloudflare_zone_id');
            $table->text('cloudflare_token');
            $table->json('allowed_record_types');
            $table->string('cname_target')->nullable();
            $table->boolean('proxied')->default(false);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subdomain_domains');
    }
}
