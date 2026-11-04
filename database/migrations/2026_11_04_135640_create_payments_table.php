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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('requisition_id')->constrained('requisitions');

            $table->string('type', 30);
            $table->string('payment_method', 20);
            $table->string('source_account', 100)->nullable();
            $table->date('paid_on');
            $table->decimal('amount', 12, 2);
            $table->string('reference', 100)->nullable();
            $table->string('proof_path', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
