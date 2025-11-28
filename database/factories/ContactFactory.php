<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $companies = ['TechCorp', 'Mobile Solutions', 'Gadget Plus', 'Electronics Hub', 'Digital Ghana', 'Phone Paradise'];
        $positions = ['Manager', 'Sales Rep', 'Owner', 'Director', 'Technician', 'Customer Service'];

        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->randomFloat(0, 0, 100) < 80 ? fake()->unique()->safeEmail() : null,
            'phone' => fake()->e164PhoneNumber(),
            'company' => fake()->randomFloat(0, 0, 100) < 60 ? fake()->randomElement($companies) : null,
            'position' => fake()->randomFloat(0, 0, 100) < 50 ? fake()->randomElement($positions) : null,
            'is_active' => fake()->boolean(90), // 90% active
        ];
    }

    /**
     * Indicate that the contact is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the contact has no company.
     */
    public function individual(): static
    {
        return $this->state(fn(array $attributes) => [
            'company' => null,
            'position' => null,
        ]);
    }
}
