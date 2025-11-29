<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\{Device, User};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DevicePhoto>
 */
class DevicePhotoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'photo_path' => 'device-photos/' . fake()->uuid() . '.jpg',
            'type' => fake()->randomElement(['condition', 'damage', 'before', 'after']),
            'description' => fake()->optional()->sentence(),
            'uploaded_by' => User::factory(),
        ];
    }
}
