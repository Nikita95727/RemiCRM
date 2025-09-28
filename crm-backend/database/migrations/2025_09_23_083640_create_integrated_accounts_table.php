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
        Schema::create('integrated_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('unipile_account_id')->unique();
            $table->enum('provider', ['telegram', 'whatsapp', 'gmail']);
            $table->string('account_name')->nullable();
            $table->string('account_email')->nullable();
            $table->string('account_username')->nullable();
            $table->enum('status', ['active', 'inactive', 'error', 'pending'])->default('pending');
            $table->timestamp('last_sync_at')->nullable();
            $table->boolean('sync_enabled')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'provider']);
            $table->index(['status', 'sync_enabled']);
            $table->index(['last_sync_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integrated_accounts');
    }
};
