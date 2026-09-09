<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ফি স্ট্রাকচার — কোন বর্ষে কোন জামাতের কোন খাতে কত টাকা।
 *
 * রেট ক্লাসভিত্তিক, ছাত্রভিত্তিক নয়: ২০০ ছাত্রের জন্য ২০০টা সারি বসাতে
 * হয় না, জামাত ও আবাসিক ধরন মিলিয়ে কয়েকটা সারিতেই পুরো মাদরাসা চলে।
 * ব্যতিক্রম student_fee_overrides-এ।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id')->nullable();

            $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('jamaat_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_head_id')->constrained()->cascadeOnDelete();

            // residential|non_residential|day_care — খালি মানে সব ধরনের
            // ছাত্রের জন্য একই রেট। আবাসিকের খানা বিল আলাদা হয় বলেই
            // এই কলামটা দরকার।
            $table->string('residency_type')->nullable();

            // টাকা সবসময় decimal — float নয়, নইলে পয়সা হারায়।
            $table->decimal('amount', 12, 2)->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // এক বর্ষে এক জামাতের এক খাতে এক আবাসিক ধরনের একটাই রেট।
            $table->unique(
                ['tenant_id', 'academic_session_id', 'jamaat_id', 'fee_head_id', 'residency_type'],
                'fee_structures_unique',
            );
            $table->index(['tenant_id', 'academic_session_id', 'jamaat_id'], 'fee_structures_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_structures');
    }
};
