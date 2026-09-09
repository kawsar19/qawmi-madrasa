<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ভর্তি আবেদন।
 *
 * Applicants are NOT students: most madrasas take far more applications than
 * they admit, so writing them into `students` would corrupt every count,
 * report and attendance register. A row here becomes a student only when
 * `enroll` runs, and that is the only place `student_id` is filled in.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id')->nullable();

            // আবেদন নম্বর — অভিভাবক এই নম্বরের স্লিপ নিয়ে যান।
            // DocumentNumberService থেকে, MAX(col)+1 নয়।
            $table->string('application_no');

            // কোন বর্ষে ভর্তি চাইছে। রোল ও ফলাফল বর্ষের সাথে বাঁধা, তাই
            // আবেদনের সময়েই বর্ষ ধরে রাখতে হয়।
            $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('jamaat_id')->constrained()->cascadeOnDelete();

            // ---- আবেদনকারীর তথ্য (students-এর মতোই, যাতে ভর্তির সময়
            // সরাসরি কপি করা যায়) ----
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('father_name');
            $table->string('mother_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('birth_certificate_no')->nullable();
            $table->string('mobile')->nullable();

            $table->string('village')->nullable();
            $table->string('post_office')->nullable();
            $table->string('union')->nullable();
            $table->string('upazila')->nullable();
            $table->string('district')->nullable();

            $table->string('residency_type')->default('non_residential');
            $table->boolean('is_orphan')->default(false);
            $table->boolean('is_poor')->default(false);

            // পূর্ববর্তী শিক্ষা — কোন মাদরাসা থেকে এসেছে।
            $table->string('previous_madrasa')->nullable();
            $table->string('previous_jamaat')->nullable();

            // ---- অভিভাবক ----
            $table->string('guardian_name')->nullable();
            $table->string('guardian_relation')->nullable();
            $table->string('guardian_mobile')->nullable();

            // আবেদিত|অনুমোদিত|বাতিল|ভর্তি সম্পন্ন
            $table->string('status')->default('applied');
            $table->date('applied_on')->nullable();

            // পাবলিক সাইট থেকে এলে 'online', অফিসে এন্ট্রি হলে 'office'.
            // The public application form writes the same table, so adding it
            // later needs no migration.
            $table->string('source')->default('office');

            // শর্টলিস্ট ও ভর্তি পরীক্ষা এখন ব্যবহার হচ্ছে না — বেশিরভাগ
            // মাদরাসা মৌখিক দেখে ভর্তি করে। কলাম রেখে দেওয়া হলো যাতে পরে
            // শুধু বাটন যোগ করলেই চলে, migration না লাগে.
            $table->decimal('exam_marks', 5, 2)->nullable();
            $table->timestamp('shortlisted_at')->nullable();

            $table->text('remarks')->nullable();

            // অনুমোদন/বাতিলের হিসাব — কে, কখন।
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();

            // ভর্তি সম্পন্ন হলে তৈরি হওয়া ছাত্র। এটাই প্রমাণ যে আবেদনটি
            // দুবার ভর্তি হয়নি।
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'application_no']);
            // তালিকার ডিফল্ট ফিল্টার: বর্ষ + অবস্থা।
            $table->index(['tenant_id', 'academic_session_id', 'status']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admissions');
    }
};
