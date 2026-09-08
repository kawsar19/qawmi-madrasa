<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id')->nullable();

            $table->string('name');
            $table->string('relation')->nullable();   // পিতা|মাতা|চাচা|ভাই|অন্যান্য
            // Deliberately NOT unique: brothers share one number, and a
            // village family often gives the same number for every child.
            $table->string('mobile')->nullable();
            $table->string('nid')->nullable();
            $table->string('occupation')->nullable();
            $table->text('address')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Composite with tenant_id first: the global scope adds
            // `WHERE tenant_id = ?` to every query, so a single-column index
            // on mobile would never be used.
            $table->index(['tenant_id', 'mobile']);
            $table->index(['tenant_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardians');
    }
};
