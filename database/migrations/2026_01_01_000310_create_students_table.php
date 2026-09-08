<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id')->nullable();

            // স্থায়ী পরিচয়। Never reassigned, never reused — a roll number
            // changes every year, this does not. Allocated by
            // DocumentNumberService, never MAX(col)+1.
            $table->string('student_uid');

            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('father_name');
            $table->string('mother_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('birth_certificate_no')->nullable();
            // Not unique: siblings share one number.
            $table->string('mobile')->nullable();
            $table->string('photo_path')->nullable();

            // ---- ঠিকানা: আলাদা ফিল্ড, যাতে "কোন উপজেলা থেকে কত ছাত্র"
            // রিপোর্ট করা যায়। A single free-text blob cannot be grouped.
            $table->string('village')->nullable();
            $table->string('post_office')->nullable();
            $table->string('union')->nullable();
            $table->string('upazila')->nullable();
            $table->string('district')->nullable();
            // Same fields again for the present address; most students leave
            // these blank, which means "same as permanent".
            $table->string('present_village')->nullable();
            $table->string('present_post_office')->nullable();
            $table->string('present_union')->nullable();
            $table->string('present_upazila')->nullable();
            $table->string('present_district')->nullable();

            // আবাসিক|অনাবাসিক|ডে-কেয়ার — drives fees and boarding.
            $table->string('residency_type')->default('non_residential');

            $table->boolean('is_orphan')->default(false);
            $table->boolean('is_poor')->default(false);

            // পড়ছে|টিসি|ঝরে পড়েছে|সনদপ্রাপ্ত
            $table->string('status')->default('active');
            $table->date('admitted_on')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'student_uid']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'name']);
            $table->index(['tenant_id', 'district', 'upazila']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
