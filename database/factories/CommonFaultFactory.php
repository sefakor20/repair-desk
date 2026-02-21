<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DeviceCategory;
use App\Models\CommonFault;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CommonFault>
 */
class CommonFaultFactory extends Factory
{
    protected $model = CommonFault::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->sentence(),
            'device_category' => $this->faker->optional()->randomElement(DeviceCategory::cases())?->value,
            'sort_order' => $this->faker->numberBetween(1, 100),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function universal(): static
    {
        return $this->state(fn(array $attributes) => [
            'device_category' => null,
        ]);
    }
}
