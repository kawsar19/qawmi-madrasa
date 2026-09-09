<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ছাত্রভিত্তিক ভিন্ন রেট — ফি স্ট্রাকচারের ব্যতিক্রম।
 *
 * ছাড় নয়, সম্পূর্ণ ভিন্ন অঙ্ক: "এই ছাত্রের বেতন ৮০০ নয়, ৫০০"। ছাড়
 * (শতকরা/মওকুফ) আলাদা টেবিলে, কারণ সেটা রেটের *উপরে* বসে।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_fee_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id')->nullable();

            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_head_id')->constrained()->cascadeOnDelete();

            $table->decimal('amount', 12, 2)->default(0);

            // কেন আলাদা রেট — মুহতামিম পরে জিজ্ঞেস করবেন।
            $table->string('reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['tenant_id', 'student_id', 'academic_session_id', 'fee_head_id'],
                'student_fee_overrides_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_fee_overrides');
    }
};
