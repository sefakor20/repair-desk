<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cash_drawer_transactions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('cash_drawer_session_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('pos_sale_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->decimal('amount', 10, 2);
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('transaction_date');
            $table->timestamps();

            $table->index('cash_drawer_session_id');
            $table->index('type');
            $table->index('transaction_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_drawer_transactions');
    }
};
