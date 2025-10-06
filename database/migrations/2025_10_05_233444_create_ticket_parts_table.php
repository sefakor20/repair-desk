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
        Schema::create('ticket_parts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('inventory_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('part_name');
            $table->integer('quantity')->default(1);
            $table->decimal('cost_price', 10, 2)->default(0);
            $table->decimal('selling_price', 10, 2)->default(0);
            $table->timestamps();

            $table->index('ticket_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_parts');
    }
};
