<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('about_pages', function (Blueprint $table) {
            $table->id();
            $table->string('eyebrow')->default('THE HOUSE · EST. 2013');
            $table->string('hero_title')->default('A quiet atelier.');
            $table->text('hero_description')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('hero_image_alt')->nullable();

            $table->text('founder_quote')->nullable();
            $table->string('founder_name')->nullable();

            $table->string('story_eyebrow')->default('OUR STORY');
            $table->string('story_title')->default('Twelve years, one rhythm.');
            $table->text('story_content')->nullable();
            $table->text('materials_content')->nullable();

            $table->string('visit_cta_text')->default('VISIT THE ATELIER');
            $table->string('visit_cta_url')->default('/contact');

            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();

            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('about_pages');
    }
};
