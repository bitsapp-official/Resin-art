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
            $table->string('video_path')->nullable()->after('images');
            $table->text('video_url')->nullable()->after('video_path');
            $table->string('video_thumbnail')->nullable()->after('video_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['video_path', 'video_url', 'video_thumbnail']);
        });
    }
};
