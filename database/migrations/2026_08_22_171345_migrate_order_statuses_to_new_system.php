<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Migrate old order statuses → new 7-status system
 *
 * Old → New mapping:
 *   PENDING          → CONFIRMED
 *   PAYMENT_PENDING  → CONFIRMED
 *   CONFIRMED        → CONFIRMED        (no change)
 *   PROCESSING       → CRAFTING
 *   READY_TO_SHIP    → PACKED
 *   SHIPPED          → SHIPPED          (no change)
 *   OUT_FOR_DELIVERY → SHIPPED
 *   DELIVERED        → DELIVERED        (no change)
 *   CANCELLED        → CANCELLED        (no change)
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('orders')->where('status', 'PENDING')->update(['status' => 'CONFIRMED']);
        DB::table('orders')->where('status', 'PAYMENT_PENDING')->update(['status' => 'CONFIRMED']);
        DB::table('orders')->where('status', 'PROCESSING')->update(['status' => 'CRAFTING']);
        DB::table('orders')->where('status', 'READY_TO_SHIP')->update(['status' => 'PACKED']);
        DB::table('orders')->where('status', 'OUT_FOR_DELIVERY')->update(['status' => 'SHIPPED']);
    }

    public function down(): void
    {
        // Rollback is approximate — CONFIRMED could have been PENDING or PAYMENT_PENDING.
        // We restore CRAFTING and PACKED only (other statuses are unchanged or ambiguous).
        DB::table('orders')->where('status', 'CRAFTING')->update(['status' => 'PROCESSING']);
        DB::table('orders')->where('status', 'PACKED')->update(['status' => 'READY_TO_SHIP']);
    }
};
