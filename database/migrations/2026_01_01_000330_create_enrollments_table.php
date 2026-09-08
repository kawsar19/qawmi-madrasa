<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id')->nullable();

            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('jamaat_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();

            // রোল এনরোলমেন্টের, ছাত্রের নয় — প্রতি বছর নতুন করে দেওয়া হয়,
            // প্রায়ই মেধাক্রম অনুসারে. Keeping it here is what lets last
            // year's roll and results stay frozen in history.
            $table->unsignedInteger('roll_no')->nullable();

            // চলমান|উত্তীর্ণ|অকৃতকার্য|ছেড়ে গেছে
            $table->string('status')->default('studying');
            $table->date('enrolled_on')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // এক ছাত্র এক বর্ষে একবারই ভর্তি হবে।
            $table->unique(['tenant_id', 'student_id', 'academic_session_id'], 'enrollments_student_session_unique');
            // রোল এক জামাতের ভেতরে, এক বর্ষে অনন্য।
            $table->unique(['tenant_id', 'academic_session_id', 'jamaat_id', 'roll_no'], 'enrollments_roll_unique');
            $table->index(['tenant_id', 'academic_session_id', 'jamaat_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
