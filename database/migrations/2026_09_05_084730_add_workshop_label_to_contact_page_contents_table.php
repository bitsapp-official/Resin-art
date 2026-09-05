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
            $table->string('workshop_label')->default('Workshop')->after('hero_subtitle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_page_contents', function (Blueprint $table) {
            $table->dropColumn('workshop_label');
        });
    }
};
