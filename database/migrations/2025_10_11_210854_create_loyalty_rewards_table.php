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
        Schema::create('loyalty_rewards', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type');
            $table->unsignedInteger('points_required');
            $table->json('reward_value');
            $table->foreignUlid('min_tier_id')->nullable()->constrained('loyalty_tiers')->nullOnDelete();
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->unsignedInteger('redemption_limit')->nullable();
            $table->unsignedInteger('times_redeemed')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('type');
            $table->index('points_required');
            $table->index('is_active');
            $table->index(['valid_from', 'valid_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_rewards');
    }
};
