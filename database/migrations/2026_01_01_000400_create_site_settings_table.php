<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * পাবলিক ওয়েবসাইটের সেটিংস — প্রতি মাদরাসার একটি সারি।
 *
 * Colours live here as plain hex and reach the page as CSS custom properties,
 * so 200 madrasas share ONE compiled stylesheet instead of needing a build
 * each. Changing a colour is a database write, not a deploy.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            // classic|modern|minimal|heritage
            $table->string('template')->default('classic');

            // ---- পরিচিতি ----
            // মাদরাসার নাম tenants টেবিলে আছে; এখানে সাইটে দেখানোর মতো
            // আলাদা নাম রাখা যায় (আরবি নাম, প্রতিষ্ঠাকাল ইত্যাদি)।
            $table->string('site_title')->nullable();
            $table->string('site_title_ar')->nullable();
            $table->string('tagline')->nullable();
            $table->string('established_year')->nullable();
            $table->text('about_short')->nullable();
            $table->text('about_full')->nullable();
            // মুহতামিমের বাণী
            $table->text('principal_message')->nullable();
            $table->string('principal_name')->nullable();

            // ---- যোগাযোগ ----
            $table->string('phone')->nullable();
            $table->string('phone_alt')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('map_embed')->nullable();

            $table->string('facebook_url')->nullable();
            $table->string('youtube_url')->nullable();

            // ---- চেহারা ----
            // CSS ভেরিয়েবল হিসেবে পেজে বসে — আলাদা বিল্ড লাগে না।
            $table->string('brand_color')->default('#15803d');
            $table->string('accent_color')->default('#a16207');
            $table->string('logo_path')->nullable();
            $table->string('hero_image_path')->nullable();

            // ---- সেকশন দেখাবে কি না ----
            $table->boolean('show_notices')->default(true);
            $table->boolean('show_teachers')->default(true);
            $table->boolean('show_gallery')->default(true);
            $table->boolean('show_admission_form')->default(true);
            // সাইট বন্ধ রেখে কাজ করার জন্য।
            $table->boolean('is_published')->default(true);

            $table->timestamps();

            // এক মাদরাসার একটিই সেটিংস সারি।
            $table->unique('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
