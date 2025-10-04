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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->json('sources')->nullable(); // JSON array of sources
            $table->text('notes')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for fast search and performance
            $table->index(['user_id']);
            $table->index(['name']);
            $table->index(['phone']);
            $table->index(['email']);
            $table->index(['created_at']);

            // Full-text search indexes for MySQL
            $table->fullText(['name', 'notes']);
        });

        // Set default value for sources column after table creation
        \DB::statement('ALTER TABLE contacts ALTER COLUMN sources SET DEFAULT (JSON_ARRAY("crm"))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
