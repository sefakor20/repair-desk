<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\StaffRole;
use App\Models\Branch;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Staff>
 */
class StaffFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'branch_id' => Branch::factory(),
            'role' => fake()->randomElement(StaffRole::cases()),
            'hire_date' => fake()->dateTimeBetween('-5 years', 'now'),
            'is_active' => true,
            'notes' => fake()->optional()->paragraph(),
        ];
    }

    /**
     * Specify the role for this staff member
     */
    public function role(StaffRole $role): self
    {
        return $this->state(fn(array $attributes) => [
            'role' => $role,
        ]);
    }

    /**
     * Create an inactive staff member
     */
    public function inactive(): self
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a branch manager
     */
    public function branchManager(): self
    {
        return $this->role(StaffRole::BranchManager);
    }

    /**
     * Create a technician
     */
    public function technician(): self
    {
        return $this->role(StaffRole::Technician);
    }

    /**
     * Create a receptionist
     */
    public function receptionist(): self
    {
        return $this->role(StaffRole::Receptionist);
    }
}
