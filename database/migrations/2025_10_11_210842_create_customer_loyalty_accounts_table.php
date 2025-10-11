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
        Schema::create('customer_loyalty_accounts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('loyalty_tier_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('total_points')->default(0);
            $table->unsignedInteger('lifetime_points')->default(0);
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamp('tier_achieved_at')->nullable();
            $table->timestamps();

            $table->unique('customer_id');
            $table->index('loyalty_tier_id');
            $table->index('total_points');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_loyalty_accounts');
    }
};
