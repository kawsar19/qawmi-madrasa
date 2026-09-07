<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id')->nullable();

            $table->string('name');                 // "১৪৪৬-১৪৪৭ হিজরি"
            $table->string('hijri_year')->nullable();
            $table->string('gregorian_year')->nullable();
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->boolean('is_current')->default(false);
            // Locked sessions reject writes at the observer level, not just in UI.
            $table->boolean('is_locked')->default(false);

            $table->timestamps();
            $table->softDeletes();

            // Composite with tenant_id first: the global scope puts
            // `WHERE tenant_id = ?` on every query, so single-column
            // indexes would never be used.
            $table->unique(['tenant_id', 'name']);
            $table->index(['tenant_id', 'is_current']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_sessions');
    }
};
