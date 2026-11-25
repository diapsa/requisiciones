<?php

namespace Database\Factories;

use App\Models\ExpenseRate;
use App\Models\ExpenseType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExpenseRate>
 */
class ExpenseRateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'expense_type_id' => ExpenseType::factory(),
            'name' => rtrim(fake()->sentence(3, false), '.'),
            'amount' => fake()->randomFloat(2, 50, 500),
            'valid_from' => '2026-01-01',
            'valid_until' => null,
        ];
    }
}
