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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique()->nullable();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('collection_id')->nullable()->constrained('collections')->nullOnDelete();
            $table->json('images')->nullable();
            $table->string('inventory_type')->default('READY_TO_SHIP'); // READY_TO_SHIP, MADE_TO_ORDER, ONE_OF_A_KIND
            $table->integer('stock')->default(0);
            $table->integer('low_stock_threshold')->default(2);
            $table->string('status')->default('published'); // draft, published, archived
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new')->default(false);
            $table->boolean('is_bestseller')->default(false);
            $table->text('care_instructions')->nullable();
            $table->text('shipping_info')->nullable();
            $table->json('attributes')->nullable();
            $table->timestamps();

            $table->index(['status', 'is_featured']);
            $table->index(['inventory_type', 'stock']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
