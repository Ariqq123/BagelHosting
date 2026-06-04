<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubdomainsTable extends Migration
{
    public function up(): void
    {
        Schema::create('subdomains', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('server_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('subdomain_domain_id');
            $table->string('name', 63);
            $table->string('fqdn')->unique();
            $table->string('type', 10);
            $table->string('content');
            $table->boolean('proxied')->default(false);
            $table->string('cloudflare_record_id')->nullable();
            $table->string('status')->default('active');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->foreign('server_id')->references('id')->on('servers')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('subdomain_domain_id')->references('id')->on('subdomain_domains')->onDelete('cascade');
            $table->unique(['subdomain_domain_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subdomains');
    }
}
