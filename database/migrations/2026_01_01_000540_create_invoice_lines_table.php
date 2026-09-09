<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ইনভয়েসের খাতভিত্তিক লাইন — বেতন, খানা বিল, সিট ভাড়া…
 *
 * fee_head_name কপি করে রাখা হয় ইচ্ছাকৃতভাবে: খাতের নাম পরে বদলালে বা
 * খাত মুছে ফেললে পুরনো রসিদে যা ছাপা হয়েছিল তা-ই থাকা চাই।
 *
 * বোর্ডিং মডিউল তৈরি হলে খানা বিল এখানেই লাইন হিসেবে বসবে, আলাদা
 * টেবিলে নয় — অভিভাবক এক জায়গায় পুরো বকেয়া দেখেন।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_head_id')->nullable()->constrained()->nullOnDelete();

            // খাত মুছে গেলেও রসিদে যা ছাপা হয়েছিল তা থাকে।
            $table->string('fee_head_name');

            // ছাড়ের আগের রেট, ছাড়, তারপর প্রকৃত টাকা। তিনটেই রাখা হয়
            // যাতে রসিদে "৮০০ − ৪০০ ছাড় = ৪০০" দেখানো যায়।
            $table->decimal('rate', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0);

            // ছাড় কীভাবে হিসাব হলো — percent|fixed|full, শূন্য হলে null.
            $table->string('discount_type')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'invoice_id'], 'invoice_lines_invoice_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_lines');
    }
};
