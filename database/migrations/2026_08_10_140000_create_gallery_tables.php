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
        Schema::create('gallery_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('slug');
            $table->index('is_active');
            $table->index('sort_order');
        });

        Schema::create('gallery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_category_id')
                  ->nullable()
                  ->constrained('gallery_categories')
                  ->nullOnDelete();
            $table->string('title');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->string('image_path');
            $table->string('image_alt');
            $table->string('caption')->nullable();
            $table->string('location')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('gallery_category_id');
            $table->index('slug');
            $table->index('is_active');
            $table->index('is_featured');
            $table->index('sort_order');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gallery_items');
        Schema::dropIfExists('gallery_categories');
    }
};
