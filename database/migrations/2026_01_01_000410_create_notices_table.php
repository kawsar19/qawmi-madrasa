<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * নোটিশ / এলান — পাবলিক সাইটে ও প্যানেলে দেখানো হয়।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->text('body')->nullable();
            // ভর্তি|পরীক্ষা|ছুটি|সাধারণ
            $table->string('category')->default('general');

            // সংযুক্তি (PDF/ছবি) — রুটিন বা ফলাফলের শিট।
            $table->string('attachment_path')->nullable();

            $table->boolean('is_published')->default(true);
            // উপরে আটকে রাখা নোটিশ (গুরুত্বপূর্ণ এলান)।
            $table->boolean('is_pinned')->default(false);
            $table->date('published_on')->nullable();
            // এই তারিখের পর তালিকা থেকে সরে যাবে; খালি = চিরস্থায়ী।
            $table->date('expires_on')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // পাবলিক সাইটের প্রধান কোয়েরি: প্রকাশিত, পিন করা আগে, তারিখ ক্রমে।
            $table->index(['tenant_id', 'is_published', 'published_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};
