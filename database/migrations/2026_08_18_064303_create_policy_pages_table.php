<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policy_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();        // shipping, return, privacy, terms
            $table->string('title');                 // "Shipping Policy"
            $table->string('hero_badge')->nullable();// "DISPATCH & DELIVERY"
            $table->string('hero_label')->nullable();// sub-heading under badge line
            $table->longText('content')->nullable(); // Rich text body
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_pages');
    }
};
