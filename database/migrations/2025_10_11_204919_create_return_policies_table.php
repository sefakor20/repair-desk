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
        Schema::create('return_policies', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('return_window_days')->default(30); // Days allowed for returns
            $table->boolean('requires_receipt')->default(true);
            $table->boolean('requires_original_packaging')->default(false);
            $table->boolean('requires_approval')->default(false); // Manager approval needed
            $table->decimal('restocking_fee_percentage', 5, 2)->default(0); // e.g., 15.00 for 15%
            $table->decimal('minimum_restocking_fee', 10, 2)->default(0);
            $table->boolean('refund_shipping')->default(false);
            $table->json('allowed_conditions')->nullable(); // ['new', 'opened', 'damaged']
            $table->json('excluded_categories')->nullable(); // Categories not eligible for returns
            $table->text('terms')->nullable(); // Additional terms and conditions
            $table->timestamps();
        });

        // Add return policy reference to pos_sales table
        Schema::table('pos_sales', function (Blueprint $table) {
            $table->ulid('return_policy_id')->nullable()->after('shift_id');
            $table->foreign('return_policy_id')->references('id')->on('return_policies')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_sales', function (Blueprint $table) {
            $table->dropForeign(['return_policy_id']);
            $table->dropColumn('return_policy_id');
        });

        Schema::dropIfExists('return_policies');
    }
};
