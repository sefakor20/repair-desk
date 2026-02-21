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
        Schema::create('device_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained('device_brands')->cascadeOnDelete();
            $table->string('name');
            $table->string('category')->default('smartphone');
            $table->json('specifications')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['brand_id', 'is_active']);
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_models');
    }
};
