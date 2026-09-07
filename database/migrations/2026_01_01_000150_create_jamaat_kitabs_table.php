<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jamaat_kitabs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id')->nullable();

            // Session-scoped curriculum: "in year X, jamaat Y studies kitab Z
            // out of N marks". Marks reference THIS row, never kitab_id
            // directly, so each year's full marks stay frozen in history even
            // if the curriculum changes later.
            $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('jamaat_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kitab_id')->constrained()->cascadeOnDelete();

            $table->unsignedSmallInteger('full_marks')->default(100);
            $table->unsignedSmallInteger('pass_marks')->default(33);
            $table->unsignedSmallInteger('written_marks')->nullable();
            $table->unsignedSmallInteger('oral_marks')->nullable();

            // Assigned teacher; FK added once employees exists.
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['tenant_id', 'academic_session_id', 'jamaat_id', 'kitab_id'],
                'jamaat_kitabs_unique'
            );
            $table->index(['tenant_id', 'academic_session_id', 'jamaat_id'], 'jamaat_kitabs_session_jamaat_idx');
            $table->index(['tenant_id', 'teacher_id'], 'jamaat_kitabs_teacher_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jamaat_kitabs');
    }
};
