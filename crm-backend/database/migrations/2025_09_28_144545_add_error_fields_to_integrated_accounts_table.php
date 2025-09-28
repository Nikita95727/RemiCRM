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
        Schema::table('integrated_accounts', function (Blueprint $table) {
            $table->text('error_message')->nullable()->after('access_token');
            $table->timestamp('last_error_at')->nullable()->after('error_message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('integrated_accounts', function (Blueprint $table) {
            $table->dropColumn(['error_message', 'last_error_at']);
        });
    }
};