<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            // Plan: bigint primary key (index-friendly), uuid as public identifier.
            $table->id();
            $table->uuid('uuid')->unique();

            $table->string('name');
            $table->string('slug')->unique();
            $table->string('eiin')->nullable();
            $table->string('madrasa_type')->default('kitab'); // kitab | hifz | mixed
            $table->text('address')->nullable();
            $table->string('logo_path')->nullable();

            $table->string('status')->default('pending'); // pending|active|suspended|expired
            $table->timestamp('trial_ends_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->json('data')->nullable();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
