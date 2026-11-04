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
        Schema::create('expense_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_type_id')
                ->constrained('expense_types');
            $table->string('name', 100);
            $table->decimal('amount', 12, 2);
            $table->date('valid_from');
            $table->date('valid_until')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_rates');
    }
};
