<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_requests', function (Blueprint $table) {
            $table->string('timeline_type')->nullable()->default('Flexible')->change();
        });
    }

    public function down(): void
    {
        Schema::table('custom_requests', function (Blueprint $table) {
            $table->string('timeline_type')->nullable(false)->change();
        });
    }
};
