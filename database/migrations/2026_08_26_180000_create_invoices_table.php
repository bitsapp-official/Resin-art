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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('custom_request_id')->nullable()->constrained('custom_requests')->nullOnDelete();
            
            // Client Contact Information
            $table->string('client_name');
            $table->string('client_email')->nullable();
            $table->string('client_phone')->nullable();
            $table->string('client_address')->nullable();
            
            // Item & Pricing Details
            $table->string('item_title');
            $table->text('item_description')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->string('currency_symbol', 10)->default('₹');
            
            // Payment Status & Mode
            $table->string('payment_status')->default('advance_paid'); // advance_paid, fully_paid, unpaid, partially_paid
            $table->string('payment_method')->nullable()->default('Direct Consultation / Bank Transfer'); // Bank Transfer, UPI, PayPal, Card, etc.
            
            $table->date('invoice_date');
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
