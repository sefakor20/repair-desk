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
        Schema::table('invoices', function (Blueprint $table) {
            // Remove old columns
            $table->dropColumn(['labor_cost', 'parts_cost', 'tax']);

            // Add new columns
            $table->decimal('tax_rate', 5, 2)->default(0)->after('subtotal');
            $table->decimal('tax_amount', 10, 2)->default(0)->after('tax_rate');
            $table->decimal('discount', 10, 2)->default(0)->after('tax_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Restore old columns
            $table->decimal('labor_cost', 10, 2)->default(0)->after('customer_id');
            $table->decimal('parts_cost', 10, 2)->default(0)->after('labor_cost');
            $table->decimal('tax', 10, 2)->default(0)->after('subtotal');

            // Remove new columns
            $table->dropColumn(['tax_rate', 'tax_amount', 'discount']);
        });
    }
};
