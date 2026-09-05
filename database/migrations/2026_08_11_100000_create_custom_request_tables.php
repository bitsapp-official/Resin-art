<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_requests', function (Blueprint $table) {
            $table->id();
            $table->string('public_reference')->unique()->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            
            $table->string('project_type');
            $table->string('project_type_other')->nullable();
            
            $table->string('width')->nullable();
            $table->string('height')->nullable();
            $table->string('depth')->nullable();
            $table->string('unit')->nullable();
            
            $table->integer('quantity')->default(1);
            
            $table->string('preferred_style')->nullable();
            $table->string('preferred_colors')->nullable();
            $table->text('idea_description');
            
            $table->string('timeline_type');
            $table->date('required_date')->nullable();
            
            $table->string('budget_range')->nullable();
            
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            
            $table->string('status')->default('submitted')->index();
            
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('custom_request_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_request_id')->constrained('custom_requests')->cascadeOnDelete();
            $table->string('type')->index(); // 'reference' or 'space'
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->string('alt_text')->nullable();
            $table->text('customer_note')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('custom_request_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_request_id')->constrained('custom_requests')->cascadeOnDelete();
            $table->string('sender_type'); // 'customer' or 'admin'
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('message');
            $table->timestamps();
        });

        Schema::create('custom_quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_request_id')->constrained('custom_requests')->cascadeOnDelete();
            $table->string('quote_reference')->unique()->index();
            
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('shipping_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            
            $table->string('deposit_type')->default('percentage'); // 'percentage' or 'fixed'
            $table->decimal('deposit_amount', 10, 2)->default(50); // e.g. 50%
            
            $table->datetime('valid_until')->nullable();
            $table->string('estimated_completion')->nullable();
            $table->text('notes')->nullable();
            
            $table->string('status')->default('draft')->index();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
        });

        Schema::create('custom_quote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_quote_id')->constrained('custom_quotes')->cascadeOnDelete();
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('custom_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_request_id')->constrained('custom_requests');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->foreignId('custom_quote_id')->constrained('custom_quotes');
            
            $table->string('order_reference')->unique()->index();
            $table->string('payment_reference')->nullable()->index();
            
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('remaining_amount', 10, 2)->default(0);
            
            $table->string('status')->default('confirmed')->index();
            
            $table->string('courier_name')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('tracking_url')->nullable();
            $table->date('shipping_date')->nullable();
            $table->datetime('delivered_at')->nullable();
            
            $table->timestamps();
        });

        Schema::create('custom_order_designs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_order_id')->constrained('custom_orders')->cascadeOnDelete();
            $table->integer('version')->default(1);
            $table->string('image_path');
            $table->text('description')->nullable();
            $table->string('status')->default('pending')->index();
            $table->text('admin_note')->nullable();
            $table->text('customer_note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('custom_order_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_order_id')->constrained('custom_orders')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('image_path')->nullable();
            $table->string('status_label')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_order_updates');
        Schema::dropIfExists('custom_order_designs');
        Schema::dropIfExists('custom_orders');
        Schema::dropIfExists('custom_quote_items');
        Schema::dropIfExists('custom_quotes');
        Schema::dropIfExists('custom_request_messages');
        Schema::dropIfExists('custom_request_images');
        Schema::dropIfExists('custom_requests');
    }
};
