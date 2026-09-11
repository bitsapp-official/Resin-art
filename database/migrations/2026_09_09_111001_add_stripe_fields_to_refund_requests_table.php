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
        Schema::table('refund_requests', function (Blueprint $table) {
            $table->string('stripe_refund_id')->nullable()->unique()->after('status');
            $table->string('stripe_refund_status')->nullable()->after('stripe_refund_id');
            $table->timestamp('refunded_at')->nullable()->after('stripe_refund_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('refund_requests', function (Blueprint $table) {
            $table->dropColumn(['stripe_refund_id', 'stripe_refund_status', 'refunded_at']);
        });
    }
};
