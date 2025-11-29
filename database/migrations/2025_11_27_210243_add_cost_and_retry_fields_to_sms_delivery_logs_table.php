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
        Schema::table('sms_delivery_logs', function (Blueprint $table) {
            // Cost tracking
            $table->decimal('cost', 10, 4)->nullable()->after('external_id')->comment('Cost in USD per SMS segment');
            $table->integer('segments')->default(1)->after('cost')->comment('Number of SMS segments sent');

            // Retry mechanism
            $table->integer('retry_count')->default(0)->after('segments')->comment('Number of retry attempts');
            $table->timestamp('last_retry_at')->nullable()->after('retry_count')->comment('Last retry timestamp');
            $table->timestamp('next_retry_at')->nullable()->after('last_retry_at')->comment('Scheduled next retry');
            $table->integer('max_retries')->default(3)->after('next_retry_at')->comment('Maximum retry attempts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_delivery_logs', function (Blueprint $table) {
            $table->dropColumn([
                'cost',
                'segments',
                'retry_count',
                'last_retry_at',
                'next_retry_at',
                'max_retries',
            ]);
        });
    }
};
