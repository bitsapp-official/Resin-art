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
        Schema::create('about_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('about_page_id')->constrained('about_pages')->cascadeOnDelete();
            $table->string('type')->default('ATELIER'); // ATELIER, CLIENT_HOME, ARTIST_WORKSHOP
            $table->string('image_path');
            $table->string('alt_text')->nullable();
            $table->string('caption')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('about_media');
    }
};
