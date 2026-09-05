<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('custom_requests', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('custom_requests', function (Blueprint $table) {
            if (Schema::hasColumn('custom_requests', 'admin_notes')) {
                $table->dropColumn('admin_notes');
            }
        });
    }
};
