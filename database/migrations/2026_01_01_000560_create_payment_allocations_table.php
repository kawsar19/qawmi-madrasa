<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * এক পেমেন্টের টাকা কোন কোন ইনভয়েসে বসল।
 *
 * ৩ মাসের বেতন একসাথে দিলে এক payment → তিনটে সারি। ইনভয়েসের
 * paid_amount এখান থেকেই পুনর্গণনা হয়, তাই এটাই একমাত্র সত্য।
 *
 * রসিদ বাতিল হলে এই সারিগুলো মুছে গিয়ে ইনভয়েস আবার বকেয়া হয় —
 * রসিদ নিজে থেকে যায়।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();

            $table->decimal('amount', 12, 2);

            $table->timestamps();

            // এক রসিদ এক ইনভয়েসে একবারই বসে।
            $table->unique(['tenant_id', 'payment_id', 'invoice_id'], 'payment_allocations_unique');
            $table->index(['tenant_id', 'invoice_id'], 'payment_allocations_invoice_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
    }
};
