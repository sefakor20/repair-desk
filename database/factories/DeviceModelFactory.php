<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DeviceCategory;
use App\Models\DeviceBrand;
use App\Models\DeviceModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeviceModel>
 */
class DeviceModelFactory extends Factory
{
    protected $model = DeviceModel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $category = $this->faker->randomElement(DeviceCategory::cases());

        return [
            'brand_id' => DeviceBrand::factory()->create(['category' => $category]),
            'name' => $this->faker->word() . ' ' . $this->faker->numerify('###'),
            'category' => $category->value,
            'specifications' => [
                'storage' => $this->faker->randomElement(['64GB', '128GB', '256GB', '512GB', '1TB']),
                'ram' => $this->faker->randomElement(['4GB', '8GB', '16GB', '32GB']),
            ],
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
