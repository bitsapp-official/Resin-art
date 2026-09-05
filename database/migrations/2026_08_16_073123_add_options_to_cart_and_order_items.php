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
        if (!Schema::hasColumn('cart_items', 'options')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->json('options')->nullable()->after('price');
            });
        }
        
        if (!Schema::hasColumn('order_items', 'options')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->json('options')->nullable()->after('unit_price');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            if (Schema::hasColumn('cart_items', 'options')) {
                $table->dropColumn('options');
            }
        });
        
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'options')) {
                $table->dropColumn('options');
            }
        });
    }
};
