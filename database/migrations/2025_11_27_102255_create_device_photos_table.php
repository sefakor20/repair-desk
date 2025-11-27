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
        Schema::create('device_photos', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('device_id')->constrained()->cascadeOnDelete();
            $table->string('photo_path');
            $table->string('type')->default('condition'); // condition, damage, before, after
            $table->text('description')->nullable();
            $table->foreignUlid('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('device_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_photos');
    }
};
