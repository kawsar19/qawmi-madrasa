<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ছাত্রভিত্তিক ছাড় — এতিম, গরিব, হাফেজ, কর্মচারীর সন্তান।
 *
 * তিন রকম ছাড় আলাদা আচরণ করে: শতকরা ছাড় রেট বাড়লে আনুপাতিক বাড়ে,
 * নির্দিষ্ট টাকা বাড়ে না, আর পূর্ণ মওকুফে কিছুই দিতে হয় না। তাই একটাই
 * কলামে না রেখে type + value.
 *
 * fee_head_id খালি মানে সব খাতে ছাড় — নইলে শুধু ঐ খাতে (যেমন বেতনে
 * ছাড় আছে কিন্তু খানা বিলে নেই)।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id')->nullable();

            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
            // খালি = সব খাতে প্রযোজ্য।
            $table->foreignId('fee_head_id')->nullable()->constrained()->cascadeOnDelete();

            // percent|fixed|full
            $table->string('type')->default('percent');
            // percent হলে ০-১০০, fixed হলে টাকা, full হলে অগ্রাহ্য।
            $table->decimal('value', 12, 2)->default(0);

            // কারণ ও কে দিল — ছাড় নিয়ে প্রশ্ন উঠবেই।
            $table->string('reason')->nullable();
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // এক বর্ষে এক ছাত্রের এক খাতে একটাই ছাড়।
            $table->unique(
                ['tenant_id', 'student_id', 'academic_session_id', 'fee_head_id'],
                'student_discounts_unique',
            );
            $table->index(['tenant_id', 'student_id', 'academic_session_id'], 'student_discounts_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_discounts');
    }
};
