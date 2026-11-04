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
        Schema::create('requisition_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisition_id')->constrained('requisitions');
            $table->foreignId('expense_type_id')->constrained('expense_types');
            $table->foreignId('vehicle_id')
                ->nullable()
                ->constrained('vehicles');

            $table->string('description', 255)->nullable();
            $table->unsignedTinyInteger('people_count')->nullable();
            $table->string('origin', 150)->nullable();
            $table->string('destination', 150)->nullable();
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->decimal('tank_capacity_liters', 6, 2)->nullable();
            $table->decimal('km_per_liter', 6, 2)->nullable();
            $table->decimal('fuel_price_per_liter', 8, 2)->nullable();
            $table->unsignedSmallInteger('days')->nullable();
            $table->unsignedTinyInteger('meals_per_day')->nullable();

            $table->decimal('unit_price', 12, 2);
            $table->decimal('quantity', 8, 2);
            $table->string('unit', 20);
            $table->decimal('amount', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisition_items');
    }
};
