<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->text('description');
            $table->string('badge_icon');
            $table->string('badge_color')->default('purple');

            $table->enum('type', ['points_milestone', 'tier_reached', 'referral_count', 'reward_redeemed', 'streak', 'special']);
            $table->json('criteria'); // e.g., {"min_points": 1000} or {"referral_count": 5}
            $table->unsignedInteger('points_reward')->default(0);

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('priority')->default(0);

            $table->timestamps();

            $table->index('type');
            $table->index('is_active');
        });

        // Pivot table for customer achievements
        Schema::create('customer_achievements', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('achievement_id')->constrained()->cascadeOnDelete();
            $table->timestamp('earned_at')->useCurrent();
            $table->boolean('is_displayed')->default(true);

            $table->unique(['customer_id', 'achievement_id']);
            $table->index('customer_id');
            $table->index('achievement_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
