<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'model' => fake()->randomElement(['Nissan NP300', 'Toyota Hilux', 'Chevrolet Aveo', 'Volkswagen Saveiro']),
            'plate_number' => strtoupper(fake()->unique()->bothify('???-###-?')),
            'tank_capacity_liters' => fake()->randomFloat(2, 40, 80),
            'km_per_liter' => fake()->randomFloat(2, 8, 18),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the vehicle can no longer be chosen.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
