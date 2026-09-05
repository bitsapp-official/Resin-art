<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add extra columns to collections table if not already present
        Schema::table('collections', function (Blueprint $table) {
            if (!Schema::hasColumn('collections', 'short_description')) {
                $table->text('short_description')->nullable()->after('description');
            }
            if (!Schema::hasColumn('collections', 'cover_image')) {
                $table->string('cover_image')->nullable()->after('image');
            }
            if (!Schema::hasColumn('collections', 'status')) {
                $table->string('status')->default('ACTIVE')->after('is_active');
            }
            if (!Schema::hasColumn('collections', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('sort_order');
            }
            if (!Schema::hasColumn('collections', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('meta_title');
            }
        });

        // Add index on status if not present
        Schema::table('collections', function (Blueprint $table) {
            $table->index(['status', 'sort_order']);
        });

        // 2. Create collection_product pivot table for Many-to-Many relationship
        if (!Schema::hasTable('collection_product')) {
            Schema::create('collection_product', function (Blueprint $table) {
                $table->id();
                $table->foreignId('collection_id')->constrained('collections')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['collection_id', 'product_id']);
                $table->index('collection_id');
                $table->index('product_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_product');

        Schema::table('collections', function (Blueprint $table) {
            $table->dropColumn(['short_description', 'cover_image', 'status', 'meta_title', 'meta_description']);
        });
    }
};
