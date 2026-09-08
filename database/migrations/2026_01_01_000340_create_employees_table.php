<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id')->nullable();

            // স্থায়ী পরিচয় — DocumentNumberService থেকে, MAX(col)+1 নয়।
            $table->string('employee_uid');

            // লগইন অ্যাকাউন্ট — সব কর্মচারীর লাগে না (বাবুর্চি, দারোয়ান),
            // তাই nullable. An ustad who enters marks gets one; the policies
            // for "own sections / own kitabs" resolve through this column.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('father_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nid_no')->nullable();
            // Not unique: a madrasa may record one family number for two staff.
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('photo_path')->nullable();

            // শিক্ষক|কর্মচারী — teachers get sections, kitabs and mark entry;
            // staff do not, so reports and pickers filter on this.
            $table->string('type')->default('teacher');
            // মুহতামিম|নাযিম|উস্তাদ|কারী|হাফেজ|মুহাসিব|বাবুর্চি|দারোয়ান|অন্যান্য
            $table->string('designation')->nullable();
            $table->string('qualification')->nullable();

            // মাসিক বেতন। টাকা সবসময় decimal — float নয়।
            $table->decimal('monthly_salary', 12, 2)->default(0);

            // ---- ঠিকানা: ছাত্রের মতোই আলাদা ফিল্ড, যাতে গ্রুপ করা যায়।
            $table->string('village')->nullable();
            $table->string('post_office')->nullable();
            $table->string('union')->nullable();
            $table->string('upazila')->nullable();
            $table->string('district')->nullable();

            // কর্মরত|ছুটিতে|অবসর|চাকরিচ্যুত
            $table->string('status')->default('active');
            $table->date('joined_on')->nullable();
            $table->date('left_on')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'employee_uid']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'type', 'status']);
            $table->index(['tenant_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
