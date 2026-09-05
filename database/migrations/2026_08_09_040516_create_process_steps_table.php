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
        Schema::create('process_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_page_id')->constrained('process_pages')->cascadeOnDelete();
            $table->string('step_number')->nullable();
            $table->string('title');
            $table->text('description');
            $table->string('image_path')->nullable();
            $table->string('image_alt')->nullable();
            $table->string('image_caption')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['process_page_id', 'sort_order', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('process_steps');
    }
};
