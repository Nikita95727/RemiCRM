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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->enum('source', ['crm', 'telegram', 'whatsapp', 'gmail'])->default('crm');
            $table->text('notes')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();

            // Indexes for fast search and performance
            $table->index(['name']);
            $table->index(['source']);
            $table->index(['phone']);
            $table->index(['email']);

            // Full-text search indexes for MySQL
            $table->fullText(['name', 'notes']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
