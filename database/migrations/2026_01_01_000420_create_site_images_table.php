<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * পাবলিক সাইটের ছবি — হোম স্লাইডার ও ফটো গ্যালারি।
 *
 * One table serves both because the row shape is identical (an image, a
 * caption, an order) and the upload screen is the same; `collection` keeps
 * them apart. Splitting them would duplicate the model, the Livewire
 * component and the upload UI for no gain.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            // slider | gallery
            $table->string('collection')->default('slider');

            // App\Support\Media builds the URL; on R2 this key already
            // carries the tenant prefix.
            $table->string('image_path');

            // স্লাইডারে ছবির উপরে বসে; গ্যালারিতে ক্যাপশন।
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            // বোতামের লেখা ও লিংক — দুটোই থাকলেই বোতাম দেখাবে।
            $table->string('link_label')->nullable();
            $table->string('link_url')->nullable();

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // The public page always asks for one tenant's active rows of one
            // collection in order — this index answers that query whole.
            $table->index(['tenant_id', 'collection', 'is_active', 'sort_order'], 'site_images_listing_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_images');
    }
};
