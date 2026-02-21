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
        Schema::table('tickets', function (Blueprint $table) {
            $table->date('repair_completion_date')->nullable()->after('actual_completion');
            $table->text('post_repair_warranty_terms')->nullable()->after('repair_completion_date');
            $table->date('post_repair_warranty_expiry')->nullable()->after('post_repair_warranty_terms');

            $table->index('post_repair_warranty_expiry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['post_repair_warranty_expiry']);
            $table->dropColumn(['repair_completion_date', 'post_repair_warranty_terms', 'post_repair_warranty_expiry']);
        });
    }
};
