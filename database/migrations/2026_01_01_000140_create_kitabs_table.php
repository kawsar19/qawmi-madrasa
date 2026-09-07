<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kitabs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            // The master book list. NOT a per-year subject — that is
            // jamaat_kitabs, which is session-scoped.
            $table->string('code');
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->foreignId('marhala_id')->nullable()->constrained()->nullOnDelete();
            $table->string('category')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'code']);
            $table->index(['tenant_id', 'marhala_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kitabs');
    }
};
