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
        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity_change'); // can be positive or negative
            $table->integer('quantity_before');
            $table->integer('quantity_after');
            $table->string('reason');
            $table->text('notes')->nullable();
            $table->foreignId('adjusted_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['inventory_item_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustments');
    }
};
