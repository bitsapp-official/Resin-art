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
        Schema::table('products', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('products', 'materials')) {
                $columnsToDrop[] = 'materials';
            }
            if (Schema::hasColumn('products', 'dimensions')) {
                $columnsToDrop[] = 'dimensions';
            }
            if (Schema::hasColumn('products', 'care_instructions')) {
                $columnsToDrop[] = 'care_instructions';
            }
            if (Schema::hasColumn('products', 'shipping_info')) {
                $columnsToDrop[] = 'shipping_info';
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('materials')->nullable();
            $table->text('dimensions')->nullable();
            $table->text('care_instructions')->nullable();
            $table->text('shipping_info')->nullable();
        });
    }
};
