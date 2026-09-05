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
        Schema::table('contact_page_contents', function (Blueprint $table) {
            if (!Schema::hasColumn('contact_page_contents', 'studio_intro')) {
                $table->text('studio_intro')->nullable()->after('studio_phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_page_contents', function (Blueprint $table) {
            if (Schema::hasColumn('contact_page_contents', 'studio_intro')) {
                $table->dropColumn('studio_intro');
            }
        });
    }
};
