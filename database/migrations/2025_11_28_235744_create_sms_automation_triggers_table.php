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
        Schema::create('sms_automation_triggers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('trigger_event'); // ticket_status_changed, appointment_reminder, etc.
            $table->json('trigger_conditions')->nullable(); // Specific conditions like status change from/to
            $table->unsignedBigInteger('sms_template_id');
            $table->integer('delay_minutes')->default(0); // Delay before sending (for reminders)
            $table->json('schedule_options')->nullable(); // For recurring or time-based triggers
            $table->boolean('is_active')->default(true);
            $table->boolean('send_to_customer')->default(true);
            $table->boolean('send_to_staff')->default(false);
            $table->json('additional_recipients')->nullable(); // Extra phone numbers
            $table->char('created_by', 26);
            $table->timestamps();

            $table->foreign('sms_template_id')->references('id')->on('sms_templates');
            $table->foreign('created_by')->references('id')->on('users');
            $table->index(['trigger_event', 'is_active']);
            $table->index('delay_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_automation_triggers');
    }
};
