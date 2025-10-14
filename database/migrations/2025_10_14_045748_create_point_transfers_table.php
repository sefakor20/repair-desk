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
        Schema::create('point_transfers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('sender_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignUlid('recipient_id')->constrained('customers')->cascadeOnDelete();

            $table->unsignedInteger('points');
            $table->text('message')->nullable();

            $table->enum('status', ['pending', 'completed', 'cancelled', 'failed'])->default('pending');
            $table->timestamp('completed_at')->nullable();

            $table->foreignUlid('sender_transaction_id')->nullable()->constrained('loyalty_transactions')->nullOnDelete();
            $table->foreignUlid('recipient_transaction_id')->nullable()->constrained('loyalty_transactions')->nullOnDelete();

            $table->timestamps();

            $table->index('sender_id');
            $table->index('recipient_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_transfers');
    }
};
