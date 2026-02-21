<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DeviceCategory;
use App\Models\DeviceBrand;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeviceBrand>
 */
class DeviceBrandFactory extends Factory
{
    protected $model = DeviceBrand::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'category' => $this->faker->randomElement(DeviceCategory::cases())->value,
            'logo_path' => null,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }
}
