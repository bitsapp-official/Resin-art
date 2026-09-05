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
        Schema::create('home_slides', function (Blueprint $table) {
            $table->id();
            $table->string('tag')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image');
            $table->string('badge_title')->nullable();
            $table->string('badge_sub')->nullable();
            $table->string('link')->nullable();
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
        Schema::dropIfExists('home_slides');
    }
};
