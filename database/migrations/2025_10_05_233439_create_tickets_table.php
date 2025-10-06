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
        Schema::create('tickets', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('ticket_number')->unique();
            $table->foreignUlid('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('device_id')->nullable()->constrained()->nullOnDelete();
            $table->text('problem_description');
            $table->text('diagnosis')->nullable();
            $table->string('status')->default('new');
            $table->string('priority')->default('normal');
            $table->foreignUlid('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUlid('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('estimated_completion')->nullable();
            $table->timestamp('actual_completion')->nullable();
            $table->timestamps();

            $table->index(['status', 'priority', 'assigned_to', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
