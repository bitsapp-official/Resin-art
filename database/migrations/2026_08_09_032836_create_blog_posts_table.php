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
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('blog_categories')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt');
            $table->longText('content');
            $table->string('featured_image')->nullable();
            $table->string('author_name')->default('Atelier Artisan');
            $table->string('reading_time')->nullable();
            $table->string('status')->default('DRAFT');
            $table->timestamp('published_at')->nullable()->index();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('og_image')->nullable();
            $table->timestamps();

            $table->index(['status', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
