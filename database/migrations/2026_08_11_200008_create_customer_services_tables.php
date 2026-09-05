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
        Schema::create('return_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->string('reason');
            $table->text('description')->nullable();
            $table->json('images')->nullable();
            $table->string('status')->default('REQUESTED');
            // REQUESTED, UNDER_REVIEW, APPROVED, REJECTED, PICKUP_PENDING, RECEIVED, RESOLVED
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('refund_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('reason');
            $table->string('status')->default('REQUESTED');
            // REQUESTED, UNDER_REVIEW, APPROVED, PROCESSING, COMPLETED, REJECTED, FAILED
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('subject');
            $table->string('category')->default('General Inquiry');
            $table->string('status')->default('OPEN');
            // OPEN, IN_PROGRESS, WAITING_FOR_CUSTOMER, RESOLVED, CLOSED
            $table->string('priority')->default('medium');
            $table->timestamps();
        });

        Schema::create('support_ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_ticket_id')->constrained('support_tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_admin')->default(false);
            $table->text('message');
            $table->json('attachments')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_ticket_messages');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('refund_requests');
        Schema::dropIfExists('return_requests');
    }
};
