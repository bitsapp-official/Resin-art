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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_reference')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('email');
            $table->string('status')->default('PENDING'); 
            // PENDING, PAYMENT_PENDING, CONFIRMED, PROCESSING, READY_TO_SHIP, SHIPPED, OUT_FOR_DELIVERY, DELIVERED, CANCELLED
            $table->string('payment_status')->default('unpaid'); // unpaid, paid, refunded, failed
            $table->string('payment_method')->default('card');
            $table->string('payment_reference')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount', 10, 2)->default(0.00);
            $table->decimal('tax', 10, 2)->default(0.00);
            $table->decimal('shipping_fee', 10, 2)->default(0.00);
            $table->decimal('grand_total', 10, 2);
            $table->json('shipping_address_snapshot');
            $table->json('billing_address_snapshot')->nullable();
            $table->string('courier')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('tracking_url')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->timestamps();

            $table->index(['order_reference', 'email']);
            $table->index(['user_id', 'status']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name');
            $table->string('sku')->nullable();
            $table->decimal('unit_price', 10, 2);
            $table->integer('quantity');
            $table->decimal('subtotal', 10, 2);
            $table->json('product_snapshot')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
