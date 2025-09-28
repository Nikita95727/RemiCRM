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
        Schema::create('contact_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('integrated_account_id')->constrained()->cascadeOnDelete();
            $table->string('external_id');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['contact_id', 'integrated_account_id']);
            $table->index(['external_id']);
            $table->index(['last_synced_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_integrations');
    }
};
