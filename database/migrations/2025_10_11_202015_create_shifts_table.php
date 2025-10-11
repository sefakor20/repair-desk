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
        Schema::create('shifts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('shift_name');
            $table->foreignUlid('opened_by')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('closed_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('open');
            $table->decimal('total_sales', 10, 2)->default(0);
            $table->integer('sales_count')->default(0);
            $table->decimal('cash_sales', 10, 2)->default(0);
            $table->decimal('card_sales', 10, 2)->default(0);
            $table->decimal('mobile_money_sales', 10, 2)->default(0);
            $table->decimal('bank_transfer_sales', 10, 2)->default(0);
            $table->text('opening_notes')->nullable();
            $table->text('closing_notes')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('opened_by');
            $table->index('started_at');
        });

        Schema::table('pos_sales', function (Blueprint $table) {
            $table->foreignUlid('shift_id')->nullable()->after('id')->constrained('shifts')->nullOnDelete();
            $table->index('shift_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_sales', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
            $table->dropIndex(['shift_id']);
            $table->dropColumn('shift_id');
        });

        Schema::dropIfExists('shifts');
    }
};
