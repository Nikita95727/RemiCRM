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
            // Add new JSON column for sources
            $table->json('sources')->nullable()->after('email');
        });

        // Copy existing source values to new sources column as JSON arrays
        \DB::statement('UPDATE contacts SET sources = JSON_ARRAY(source) WHERE source IS NOT NULL');
        \DB::statement("UPDATE contacts SET sources = JSON_ARRAY('crm') WHERE sources IS NULL");

        Schema::table('contacts', function (Blueprint $table) {
            // Drop the old enum source column
            $table->dropColumn('source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            // Convert back to enum (taking first source from array)
            $table->enum('source', ['crm', 'telegram', 'whatsapp', 'gmail'])->default('crm')->after('email');
        });

        // Copy first source back to enum column
        \DB::statement("UPDATE contacts SET source = JSON_UNQUOTE(JSON_EXTRACT(sources, '$[0]')) WHERE sources IS NOT NULL");

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('sources');
        });
    }
};
