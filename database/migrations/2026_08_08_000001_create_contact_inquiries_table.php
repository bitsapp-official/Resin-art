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
        Schema::create('contact_inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('public_reference')->unique()->index();
            $table->string('name');
            $table->string('email')->index();
            $table->string('phone')->nullable();
            $table->string('inquiry_type')->index();
            $table->string('subject');
            $table->text('message');
            $table->string('status')->default('new')->index();
            $table->string('priority')->default('normal')->index();
            $table->text('admin_notes')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_inquiries');
    }
};
