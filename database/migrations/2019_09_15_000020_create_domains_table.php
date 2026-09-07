<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            // Full hostname (madrasa.app.com OR madrasa.edu.bd) — resolved by
            // InitializeTenancyByDomain, so both kinds live in this one table.
            $table->string('domain', 255)->unique();
            $table->foreignId('tenant_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();

            $table->boolean('is_primary')->default(false);
            $table->string('type')->default('subdomain'); // subdomain | custom
            $table->string('ssl_status')->default('pending'); // pending|active|failed
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
