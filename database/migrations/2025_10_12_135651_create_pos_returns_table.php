<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pos_returns', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('return_number')->unique();
            $table->foreignUlid('original_sale_id')->constrained('pos_sales')->cascadeOnDelete();
            $table->foreignUlid('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('processed_by')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('shift_id')->nullable()->constrained('shifts')->nullOnDelete();

            $table->string('return_reason');
            $table->text('return_notes')->nullable();
            $table->string('status')->default('pending');

            $table->decimal('subtotal_returned', 10, 2);
            $table->decimal('tax_returned', 10, 2)->default(0);
            $table->decimal('restocking_fee', 10, 2)->default(0);
            $table->decimal('total_refund_amount', 10, 2);

            $table->string('refund_method');
            $table->string('refund_reference')->nullable();
            $table->json('refund_metadata')->nullable();
            $table->timestamp('refunded_at')->nullable();

            $table->boolean('inventory_restored')->default(false);
            $table->timestamp('return_date');
            $table->timestamps();

            $table->index(['return_date', 'status']);
            $table->index('processed_by');
        });

        Schema::create('pos_return_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('pos_return_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('original_sale_item_id')->constrained('pos_sale_items')->cascadeOnDelete();
            $table->foreignUlid('inventory_item_id')->constrained()->cascadeOnDelete();

            $table->integer('quantity_returned');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('line_refund_amount', 10, 2);

            $table->string('item_condition')->nullable();
            $table->text('item_notes')->nullable();

            $table->timestamps();

            $table->index('pos_return_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_return_items');
        Schema::dropIfExists('pos_returns');
    }
};
