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
        Schema::create('process_pages', function (Blueprint $table) {
            $table->id();
            $table->string('eyebrow')->default('OUR PROCESS');
            $table->string('title')->default('Six weeks, one object.');
            $table->text('description')->default('From timber selection to the final hand-polish, nothing here is hurried.');
            $table->string('cta_title')->default('Have a custom piece in mind?');
            $table->string('cta_button_text')->default('SUBMIT YOUR REQUIREMENTS');
            $table->string('cta_url')->default('/custom');
            $table->string('status')->default('PUBLISHED');
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('process_pages');
    }
};
