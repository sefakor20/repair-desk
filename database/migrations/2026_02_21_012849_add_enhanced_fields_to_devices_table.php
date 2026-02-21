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
            $table->string('device_type')->nullable()->after('type');
            $table->foreignId('brand_id')->nullable()->after('brand')->constrained('device_brands')->nullOnDelete();
            $table->foreignId('model_id')->nullable()->after('model')->constrained('device_models')->nullOnDelete();
            $table->json('diagnosed_faults')->nullable()->after('notes');

            $table->index('device_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropForeign(['model_id']);
            $table->dropIndex(['device_type']);
            $table->dropColumn(['device_type', 'brand_id', 'model_id', 'diagnosed_faults']);
        });
    }
};
