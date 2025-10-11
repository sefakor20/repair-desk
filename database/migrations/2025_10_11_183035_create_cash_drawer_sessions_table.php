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
        Schema::create('cash_drawer_sessions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('opened_by')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('closed_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->decimal('opening_balance', 10, 2);
            $table->decimal('expected_balance', 10, 2)->default(0);
            $table->decimal('actual_balance', 10, 2)->nullable();
            $table->decimal('cash_sales', 10, 2)->default(0);
            $table->decimal('cash_in', 10, 2)->default(0);
            $table->decimal('cash_out', 10, 2)->default(0);
            $table->decimal('discrepancy', 10, 2)->nullable();
            $table->string('status')->default('open');
            $table->text('opening_notes')->nullable();
            $table->text('closing_notes')->nullable();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('opened_by');
            $table->index('opened_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_drawer_sessions');
    }
};
