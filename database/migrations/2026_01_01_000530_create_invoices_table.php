<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * মাসিক বিল — এক ছাত্রের এক মাসের ইনভয়েস।
 *
 * billing_month "YYYY-MM" স্ট্রিং, তারিখ নয়: মাসটাই একক, দিন অর্থহীন।
 * এর উপরের unique index-ই বিল রান idempotent করে — PHP-তে "আগে চালানো
 * হয়েছে কিনা" চেক করলে দুটো রিকোয়েস্ট একসাথে এলে দুটোই পাশ করে যেত।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id')->nullable();

            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
            // বিলের সময় ছাত্র কোন জামাতে ছিল — পরে ক্লাস বদলালেও পুরনো
            // বিলের ইতিহাস ঠিক থাকে।
            $table->foreignId('jamaat_id')->nullable()->constrained()->nullOnDelete();

            $table->string('invoice_no');
            // "2026-01" — এক ছাত্রের এক মাসে একটাই বিল।
            $table->string('billing_month', 7);
            $table->date('issued_on');
            $table->date('due_on')->nullable();

            // মোট = লাইনগুলোর যোগফল; ছাড় বাদ দিয়ে net.
            $table->decimal('gross_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2)->default(0);

            // allocation থেকে পুনর্গণনা হয় — হাতে বাড়ানো-কমানো হয় না।
            // বকেয়া আলাদা কলামে রাখা হয়নি: net_amount - paid_amount,
            // নইলে দুই জায়গায় সত্য থেকে তারা আলাদা হয়ে যেত।
            $table->decimal('paid_amount', 12, 2)->default(0);

            // unpaid|partial|paid|cancelled
            $table->string('status')->default('unpaid');

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // বিল রান দুবার চালালেও দ্বিগুণ বিল হবে না — এই index-ই গ্যারান্টি।
            $table->unique(
                ['tenant_id', 'student_id', 'academic_session_id', 'billing_month'],
                'invoices_student_month_unique',
            );
            $table->unique(['tenant_id', 'invoice_no'], 'invoices_no_unique');
            // বকেয়া রিপোর্ট এই index-এ চলে।
            $table->index(['tenant_id', 'status', 'billing_month'], 'invoices_due_lookup');
            $table->index(['tenant_id', 'student_id', 'status'], 'invoices_student_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
