<?php

namespace Database\Factories;

use App\Models\BankAccount;
use App\Models\BusinessUnit;
use App\Models\Department;
use App\Models\Project;
use App\Models\Requisition;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Requisition>
 */
class RequisitionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sequence = fake()->unique()->numberBetween(1, 99999);

        return [
            'number' => sprintf('%03d_TEST', $sequence),
            'sequence' => $sequence,
            'requester_id' => User::factory(),
            'bank_account_id' => fn (array $attributes) => BankAccount::factory()->state(['user_id' => $attributes['requester_id']]),
            'department_id' => Department::factory(),
            'business_unit_id' => BusinessUnit::factory(),
            'project_id' => Project::factory(),
            'service_description' => fake()->sentence(),
            'requested_on' => now()->toDateString(),
            'starts_on' => now()->addDays(3)->toDateString(),
            'ends_on' => now()->addDays(5)->toDateString(),
            'status' => 'draft',
        ];
    }
}
