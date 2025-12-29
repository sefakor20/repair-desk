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
        Schema::table('customers', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['branch_id']);

            // Modify the column to char(36) to match branches table
            $table->char('branch_id', 36)->nullable()->change();

            // Re-add foreign key constraint
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['branch_id']);

            // Change back to ulid (26 chars)
            $table->char('branch_id', 26)->nullable()->change();

            // Re-add foreign key constraint
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
        });
    }
};
