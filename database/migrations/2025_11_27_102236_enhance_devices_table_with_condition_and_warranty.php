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
        Schema::table('devices', function (Blueprint $table) {
            // Device condition assessment
            $table->string('condition')->nullable()->after('notes'); // excellent, good, fair, poor, damaged
            $table->text('condition_notes')->nullable()->after('condition');

            // Warranty information
            $table->date('purchase_date')->nullable()->after('condition_notes');
            $table->date('warranty_expiry')->nullable()->after('purchase_date');
            $table->string('warranty_provider')->nullable()->after('warranty_expiry');
            $table->text('warranty_notes')->nullable()->after('warranty_provider');

            // Additional device details
            $table->string('color')->nullable()->after('model');
            $table->string('storage_capacity')->nullable()->after('color'); // e.g., "256GB"
            $table->string('password_pin')->nullable()->after('warranty_notes'); // Encrypted storage recommended

            // Multi-tenancy support
            $table->char('branch_id', 36)->nullable()->after('customer_id');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->index('branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn([
                'condition',
                'condition_notes',
                'purchase_date',
                'warranty_expiry',
                'warranty_provider',
                'warranty_notes',
                'color',
                'storage_capacity',
                'password_pin',
                'branch_id',
            ]);
        });
    }
};
