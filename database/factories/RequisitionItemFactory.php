<?php

namespace Database\Factories;

use App\Models\ExpenseType;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RequisitionItem>
 */
class RequisitionItemFactory extends Factory
{
    /**
     * Define the model's default state: a fixed-amount item.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $unitPrice = fake()->randomFloat(2, 50, 1000);

        return [
            'requisition_id' => Requisition::factory(),
            'expense_type_id' => ExpenseType::factory(),
            'unit_price' => $unitPrice,
            'quantity' => 1,
            'unit' => 'ÚNICO',
            'amount' => $unitPrice,
        ];
    }
}
