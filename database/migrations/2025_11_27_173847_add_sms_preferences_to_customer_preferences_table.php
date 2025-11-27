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
        Schema::table('customer_preferences', function (Blueprint $table) {
            // SMS notification preferences
            $table->boolean('sms_enabled')->default(true)->after('newsletter');
            $table->boolean('sms_ticket_updates')->default(true)->after('sms_enabled');
            $table->boolean('sms_repair_completed')->default(true)->after('sms_ticket_updates');
            $table->boolean('sms_invoice_reminders')->default(true)->after('sms_repair_completed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_preferences', function (Blueprint $table) {
            $table->dropColumn([
                'sms_enabled',
                'sms_ticket_updates',
                'sms_repair_completed',
                'sms_invoice_reminders',
            ]);
        });
    }
};
