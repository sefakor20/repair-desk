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
        Schema::create('customer_preferences', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('customer_id')->unique()->constrained()->cascadeOnDelete();

            // Email notification preferences
            $table->boolean('notify_points_earned')->default(true);
            $table->boolean('notify_reward_available')->default(true);
            $table->boolean('notify_tier_upgrade')->default(true);
            $table->boolean('notify_points_expiring')->default(true);
            $table->boolean('notify_referral_success')->default(true);

            // Communication preferences
            $table->boolean('marketing_emails')->default(false);
            $table->boolean('newsletter')->default(false);

            $table->timestamps();

            $table->index('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_preferences');
    }
};
