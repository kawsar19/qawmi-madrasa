<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            // Every human-facing sequence lives here: student_uid, roll_no,
            // receipt_no, invoice_no, donation receipt... Allocated with
            // SELECT ... FOR UPDATE so concurrent requests never collide.
            $table->string('entity');
            // Scope within the entity, e.g. "session:3|jamaat:12" for a roll.
            $table->string('scope_key')->default('');
            $table->unsignedBigInteger('last_number')->default(0);
            $table->string('prefix')->nullable();

            $table->timestamps();

            $table->unique(['tenant_id', 'entity', 'scope_key'], 'document_numbers_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_numbers');
    }
};
