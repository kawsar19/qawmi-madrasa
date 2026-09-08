<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * শাখা ইনচার্জের ফরেন কি — sections টেবিল employees-এর আগে তৈরি হয়,
 * তাই কলামটি সেখানে খালি unsignedBigInteger হিসেবে রাখা ছিল।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
        });
    }
};
