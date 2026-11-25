<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Give the boolean flags a default and let drafts have no approved amount yet.
     */
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->change();
        });

        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->change();
        });

        Schema::table('expense_types', function (Blueprint $table) {
            $table->boolean('is_requestable')->default(true)->change();
        });

        Schema::table('requisitions', function (Blueprint $table) {
            $table->decimal('approved_amount', 12, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->boolean('is_active')->change();
        });

        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->boolean('is_active')->change();
        });

        Schema::table('expense_types', function (Blueprint $table) {
            $table->boolean('is_requestable')->change();
        });

        Schema::table('requisitions', function (Blueprint $table) {
            $table->decimal('approved_amount', 12, 2)->nullable(false)->change();
        });
    }
};
