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
        // 1. Timeline Steps Table
        Schema::create('about_timeline_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('about_page_id')->constrained('about_pages')->cascadeOnDelete();
            $table->string('year');
            $table->string('title');
            $table->text('description');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['about_page_id', 'sort_order', 'is_active']);
        });

        // 2. Artisans / Craft Team Table
        Schema::create('about_artisans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('about_page_id')->constrained('about_pages')->cascadeOnDelete();
            $table->string('name');
            $table->string('role');
            $table->string('image_path')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['about_page_id', 'sort_order', 'is_active']);
        });

        // 3. Drop legacy about_media table if exists
        Schema::dropIfExists('about_media');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('about_artisans');
        Schema::dropIfExists('about_timeline_steps');
    }
};
