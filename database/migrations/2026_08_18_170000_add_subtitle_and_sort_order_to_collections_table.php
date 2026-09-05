<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            if (!Schema::hasColumn('collections', 'subtitle')) {
                $table->string('subtitle')->nullable()->after('name');
            }
            if (!Schema::hasColumn('collections', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('image');
            }
        });
    }

    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->dropColumn(['subtitle', 'sort_order']);
        });
    }
};
