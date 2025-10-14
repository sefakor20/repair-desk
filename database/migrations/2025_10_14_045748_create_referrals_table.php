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
        Schema::create('referrals', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('referrer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignUlid('referred_id')->nullable()->constrained('customers')->nullOnDelete();

            $table->string('referral_code')->unique();
            $table->string('referred_email')->nullable();
            $table->string('referred_name')->nullable();

            $table->enum('status', ['pending', 'completed', 'expired'])->default('pending');
            $table->unsignedInteger('points_awarded')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            $table->index('referrer_id');
            $table->index('referred_id');
            $table->index('referral_code');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
