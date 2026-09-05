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
        Schema::create('contact_page_contents', function (Blueprint $table) {
            $table->id();
            $table->string('hero_badge')->default('Correspondence');
            $table->string('hero_title')->default('Write to the atelier.');
            $table->text('hero_subtitle')->nullable();
            $table->text('studio_address')->nullable();
            $table->text('studio_hours')->nullable();
            $table->string('studio_email')->nullable();
            $table->string('studio_phone')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_page_contents');
    }
};
