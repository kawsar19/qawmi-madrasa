<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * আদায় — এক রসিদ।
 *
 * রসিদ ইনভয়েসের সাথে সরাসরি বাঁধা নয়। অভিভাবক ৩ মাসের বেতন একসাথে
 * দিলে একটাই রসিদ পান, আর টাকা payment_allocations দিয়ে তিনটে ইনভয়েসে
 * ভাগ হয়। এই আলাদা রাখাটাই কয়েক মাস একসাথে, আংশিক আদায় ও অগ্রিম জমা
 * — তিনটেই সমাধান করে।
 *
 * রসিদ কখনো ডিলিট হয় না, শুধু বাতিল: অভিভাবকের হাতে কাগজের কপি থাকে,
 * মুছে ফেললে দুই পক্ষের হিসাব আলাদা হয়ে যায়।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id')->nullable();

            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();

            // DocumentNumberService থেকে — MAX(col)+1 নয়।
            $table->string('receipt_no');
            $table->date('paid_on');

            // মোট যত টাকা নেওয়া হলো। এর কতটা ইনভয়েসে বসল তা
            // allocations-এ; বাকিটা অগ্রিম জমা।
            $table->decimal('amount', 12, 2);

            // cash|bank|mobile — bKash ইত্যাদি MVP-তে হাতে এন্ট্রি,
            // গেটওয়ে নেই।
            $table->string('method')->default('cash');
            $table->string('reference')->nullable();

            // কার হাতে টাকা গেল।
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();

            $table->text('notes')->nullable();

            // ---- বাতিল (ডিলিট নয়) ----
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('cancel_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'receipt_no'], 'payments_receipt_unique');
            $table->index(['tenant_id', 'student_id'], 'payments_student_lookup');
            $table->index(['tenant_id', 'paid_on'], 'payments_date_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
