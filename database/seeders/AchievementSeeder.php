<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $achievements = [
            // Points Milestones
            [
                'name' => 'First Steps',
                'description' => 'Earn your first 100 loyalty points',
                'badge_icon' => '🌟',
                'badge_color' => 'blue',
                'type' => 'points_milestone',
                'criteria' => json_encode(['points' => 100]),
                'points_reward' => 50,
                'priority' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Point Collector',
                'description' => 'Accumulate 500 loyalty points',
                'badge_icon' => '💎',
                'badge_color' => 'purple',
                'type' => 'points_milestone',
                'criteria' => json_encode(['points' => 500]),
                'points_reward' => 100,
                'priority' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Points Master',
                'description' => 'Reach 1,000 loyalty points',
                'badge_icon' => '👑',
                'badge_color' => 'yellow',
                'type' => 'points_milestone',
                'criteria' => json_encode(['points' => 1000]),
                'points_reward' => 250,
                'priority' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Elite Member',
                'description' => 'Achieve 5,000 loyalty points',
                'badge_icon' => '🏆',
                'badge_color' => 'gold',
                'type' => 'points_milestone',
                'criteria' => json_encode(['points' => 5000]),
                'points_reward' => 500,
                'priority' => 4,
                'is_active' => true,
            ],

            // Tier Achievements
            [
                'name' => 'Silver Status',
                'description' => 'Reach Silver loyalty tier',
                'badge_icon' => '🥈',
                'badge_color' => 'zinc',
                'type' => 'tier_reached',
                'criteria' => json_encode(['tier_name' => 'Silver']),
                'points_reward' => 100,
                'priority' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Gold Status',
                'description' => 'Reach Gold loyalty tier',
                'badge_icon' => '🥇',
                'badge_color' => 'amber',
                'type' => 'tier_reached',
                'criteria' => json_encode(['tier_name' => 'Gold']),
                'points_reward' => 250,
                'priority' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Platinum Status',
                'description' => 'Reach Platinum loyalty tier',
                'badge_icon' => '💫',
                'badge_color' => 'cyan',
                'type' => 'tier_reached',
                'criteria' => json_encode(['tier_name' => 'Platinum']),
                'points_reward' => 500,
                'priority' => 7,
                'is_active' => true,
            ],

            // Referral Achievements
            [
                'name' => 'Friend Magnet',
                'description' => 'Successfully refer 3 friends',
                'badge_icon' => '🤝',
                'badge_color' => 'green',
                'type' => 'referral_count',
                'criteria' => json_encode(['count' => 3]),
                'points_reward' => 150,
                'priority' => 8,
                'is_active' => true,
            ],
            [
                'name' => 'Social Butterfly',
                'description' => 'Successfully refer 10 friends',
                'badge_icon' => '🦋',
                'badge_color' => 'pink',
                'type' => 'referral_count',
                'criteria' => json_encode(['count' => 10]),
                'points_reward' => 500,
                'priority' => 9,
                'is_active' => true,
            ],
            [
                'name' => 'Ambassador',
                'description' => 'Successfully refer 25 friends',
                'badge_icon' => '🎖️',
                'badge_color' => 'red',
                'type' => 'referral_count',
                'criteria' => json_encode(['count' => 25]),
                'points_reward' => 1000,
                'priority' => 10,
                'is_active' => true,
            ],

            // Reward Redemption Achievements
            [
                'name' => 'First Reward',
                'description' => 'Redeem your first reward',
                'badge_icon' => '🎁',
                'badge_color' => 'purple',
                'type' => 'reward_redeemed',
                'criteria' => json_encode(['count' => 1]),
                'points_reward' => 50,
                'priority' => 11,
                'is_active' => true,
            ],
            [
                'name' => 'Reward Hunter',
                'description' => 'Redeem 5 rewards',
                'badge_icon' => '🎯',
                'badge_color' => 'orange',
                'type' => 'reward_redeemed',
                'criteria' => json_encode(['count' => 5]),
                'points_reward' => 200,
                'priority' => 12,
                'is_active' => true,
            ],

            // Special Achievements
            [
                'name' => 'Early Adopter',
                'description' => 'One of the first customers to join our loyalty program',
                'badge_icon' => '🚀',
                'badge_color' => 'indigo',
                'type' => 'special',
                'criteria' => json_encode(['manual' => true]),
                'points_reward' => 100,
                'priority' => 13,
                'is_active' => true,
            ],
            [
                'name' => 'Generous Gifter',
                'description' => 'Transfer points to 5 different customers',
                'badge_icon' => '💝',
                'badge_color' => 'rose',
                'type' => 'special',
                'criteria' => json_encode(['transfer_count' => 5]),
                'points_reward' => 300,
                'priority' => 14,
                'is_active' => true,
            ],
        ];

        foreach ($achievements as $achievement) {
            Achievement::create($achievement);
        }
    }
}
