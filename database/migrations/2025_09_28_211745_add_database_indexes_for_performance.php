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
        Schema::table('contacts', function (Blueprint $table) {
            // Only add indexes that don't exist yet
            
            // Composite index for user_id + email (common combination)
            if (!$this->indexExists('contacts', 'contacts_user_email_index')) {
                $table->index(['user_id', 'email'], 'contacts_user_email_index');
            }
            
            // Composite index for user_id + phone (common combination)  
            if (!$this->indexExists('contacts', 'contacts_user_phone_index')) {
                $table->index(['user_id', 'phone'], 'contacts_user_phone_index');
            }
            
            // Index for updated_at (sorting)
            if (!$this->indexExists('contacts', 'contacts_updated_at_index')) {
                $table->index('updated_at', 'contacts_updated_at_index');
            }
        });

        Schema::table('integrated_accounts', function (Blueprint $table) {
            // Add only new indexes for integrated_accounts
            if (!$this->indexExists('integrated_accounts', 'integrated_accounts_user_id_index')) {
                $table->index('user_id', 'integrated_accounts_user_id_index');
            }
            if (!$this->indexExists('integrated_accounts', 'integrated_accounts_provider_index')) {
                $table->index('provider', 'integrated_accounts_provider_index');
            }
            if (!$this->indexExists('integrated_accounts', 'integrated_accounts_status_index')) {
                $table->index('status', 'integrated_accounts_status_index');
            }
            if (!$this->indexExists('integrated_accounts', 'integrated_accounts_user_provider_index')) {
                $table->index(['user_id', 'provider'], 'integrated_accounts_user_provider_index');
            }
            if (!$this->indexExists('integrated_accounts', 'integrated_accounts_unipile_id_index')) {
                $table->index('unipile_account_id', 'integrated_accounts_unipile_id_index');
            }
        });

        Schema::table('contact_integrations', function (Blueprint $table) {
            // Add only new indexes for contact_integrations
            if (!$this->indexExists('contact_integrations', 'contact_integrations_contact_id_index')) {
                $table->index('contact_id', 'contact_integrations_contact_id_index');
            }
            if (!$this->indexExists('contact_integrations', 'contact_integrations_account_id_index')) {
                $table->index('integrated_account_id', 'contact_integrations_account_id_index');
            }
            if (!$this->indexExists('contact_integrations', 'contact_integrations_external_id_index')) {
                $table->index('external_id', 'contact_integrations_external_id_index');
            }
        });
    }

    /**
     * Check if an index exists on a table
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = \DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return !empty($indexes);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            // Drop only indexes we might have created
            if ($this->indexExists('contacts', 'contacts_user_email_index')) {
                $table->dropIndex('contacts_user_email_index');
            }
            if ($this->indexExists('contacts', 'contacts_user_phone_index')) {
                $table->dropIndex('contacts_user_phone_index');
            }
            if ($this->indexExists('contacts', 'contacts_updated_at_index')) {
                $table->dropIndex('contacts_updated_at_index');
            }
        });

        Schema::table('integrated_accounts', function (Blueprint $table) {
            if ($this->indexExists('integrated_accounts', 'integrated_accounts_user_id_index')) {
                $table->dropIndex('integrated_accounts_user_id_index');
            }
            if ($this->indexExists('integrated_accounts', 'integrated_accounts_provider_index')) {
                $table->dropIndex('integrated_accounts_provider_index');
            }
            if ($this->indexExists('integrated_accounts', 'integrated_accounts_status_index')) {
                $table->dropIndex('integrated_accounts_status_index');
            }
            if ($this->indexExists('integrated_accounts', 'integrated_accounts_user_provider_index')) {
                $table->dropIndex('integrated_accounts_user_provider_index');
            }
            if ($this->indexExists('integrated_accounts', 'integrated_accounts_unipile_id_index')) {
                $table->dropIndex('integrated_accounts_unipile_id_index');
            }
        });

        Schema::table('contact_integrations', function (Blueprint $table) {
            if ($this->indexExists('contact_integrations', 'contact_integrations_contact_id_index')) {
                $table->dropIndex('contact_integrations_contact_id_index');
            }
            if ($this->indexExists('contact_integrations', 'contact_integrations_account_id_index')) {
                $table->dropIndex('contact_integrations_account_id_index');
            }
            if ($this->indexExists('contact_integrations', 'contact_integrations_external_id_index')) {
                $table->dropIndex('contact_integrations_external_id_index');
            }
        });
    }
};