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
        Schema::create('device_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('device_id')->constrained('devices')->cascadeOnDelete();
            $table->foreignUlid('ticket_id')->nullable()->constrained('tickets')->cascadeOnDelete();
            $table->string('type');
            $table->json('assessment_data')->nullable();
            $table->json('photos')->nullable();
            $table->foreignUlid('assessed_by')->constrained('users');
            $table->timestamp('assessed_at');
            $table->timestamps();

            $table->index(['device_id', 'type']);
            $table->index('ticket_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_assessments');
    }
};
