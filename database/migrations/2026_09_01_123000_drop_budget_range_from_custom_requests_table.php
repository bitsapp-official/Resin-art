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
        Schema::table('custom_requests', function (Blueprint $table) {
            if (Schema::hasColumn('custom_requests', 'budget_range')) {
                $table->dropColumn('budget_range');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('custom_requests', 'budget_range')) {
                $table->string('budget_range')->nullable();
            }
        });
    }
};
