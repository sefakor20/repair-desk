<?php

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
        // Add branch_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->char('branch_id', 36)->nullable()->after('role');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->index('branch_id');
        });

        // Add branch_id to tickets table
        Schema::table('tickets', function (Blueprint $table) {
            $table->char('branch_id', 36)->nullable()->after('customer_id');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->index('branch_id');
        });

        // Add branch_id to inventory_items table
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->char('branch_id', 36)->nullable()->after('name');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->index('branch_id');
        });

        // Add branch_id to pos_sales table
        Schema::table('pos_sales', function (Blueprint $table) {
            $table->char('branch_id', 36)->nullable()->after('sale_number');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->index('branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('pos_sales', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
};
