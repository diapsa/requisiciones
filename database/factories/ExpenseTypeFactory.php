<?php

namespace Database\Factories;

use App\Models\ExpenseType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExpenseType>
 */
class ExpenseTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->lexify('type_????'),
            'name' => ucfirst(fake()->unique()->words(2, true)),
            'calculation_method' => 'fixed',
            'default_unit' => 'ÚNICO',
            'is_requestable' => true,
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }
}
