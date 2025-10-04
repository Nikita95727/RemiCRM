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
        // Create integrated_accounts table
        Schema::create('integrated_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('unipile_account_id')->unique();
            $table->enum('provider', ['telegram', 'whatsapp', 'google_oauth']);
            $table->string('account_name')->nullable();
            $table->string('account_email')->nullable();
            $table->string('account_username')->nullable();
            $table->text('access_token')->nullable();
            $table->enum('status', ['active', 'inactive', 'error', 'pending'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('last_error_at')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->boolean('sync_enabled')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'provider']);
            $table->index(['status', 'sync_enabled']);
            $table->index(['last_sync_at']);
        });

        // Create contact_integrations table
        Schema::create('contact_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('integrated_account_id')->constrained()->cascadeOnDelete();
            $table->string('external_id');
            $table->string('provider_contact_id')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->enum('sync_status', ['pending', 'synced', 'error'])->default('pending');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['contact_id', 'integrated_account_id']);
            $table->index(['external_id']);
            $table->index(['last_synced_at']);
            $table->index(['sync_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_integrations');
        Schema::dropIfExists('integrated_accounts');
    }
};
