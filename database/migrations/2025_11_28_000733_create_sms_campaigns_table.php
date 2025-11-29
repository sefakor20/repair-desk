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
        Schema::create('sms_campaigns', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->text('message');
            $table->string('status')->default('draft'); // draft, scheduled, sending, completed, cancelled
            $table->json('segment_rules')->nullable(); // Customer segment criteria
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('total_recipients')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->decimal('estimated_cost', 10, 4)->nullable();
            $table->decimal('actual_cost', 10, 4)->nullable();
            $table->foreignUlid('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('scheduled_at');
        });

        // Add campaign_id to sms_delivery_logs
        Schema::table('sms_delivery_logs', function (Blueprint $table) {
            $table->foreignUlid('campaign_id')->nullable()->after('id')->constrained('sms_campaigns')->nullOnDelete();
            $table->index('campaign_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_delivery_logs', function (Blueprint $table) {
            $table->dropForeign(['campaign_id']);
            $table->dropColumn('campaign_id');
        });

        Schema::dropIfExists('sms_campaigns');
    }
};
