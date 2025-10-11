<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\LoyaltyTier;
use Illuminate\Database\Seeder;

class LoyaltyTierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiers = [
            [
                'name' => 'Bronze',
                'description' => 'Entry level tier for new customers. Start earning points on every purchase!',
                'min_points' => 0,
                'points_multiplier' => 1.0,
                'discount_percentage' => 0,
                'color' => '#CD7F32',
                'priority' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Silver',
                'description' => 'Earn 25% more points and enjoy 5% off on all purchases.',
                'min_points' => 1000,
                'points_multiplier' => 1.25,
                'discount_percentage' => 5,
                'color' => '#C0C0C0',
                'priority' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Gold',
                'description' => 'Premium tier with 50% bonus points and 10% discount on all purchases.',
                'min_points' => 5000,
                'points_multiplier' => 1.5,
                'discount_percentage' => 10,
                'color' => '#FFD700',
                'priority' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Platinum',
                'description' => 'Elite tier for our best customers. Double points and 15% off everything!',
                'min_points' => 15000,
                'points_multiplier' => 2.0,
                'discount_percentage' => 15,
                'color' => '#E5E4E2',
                'priority' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($tiers as $tier) {
            LoyaltyTier::create($tier);
        }
    }
}
