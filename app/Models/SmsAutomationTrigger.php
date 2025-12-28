<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsAutomationTrigger extends Model
{
    protected $fillable = [
        'name',
        'description',
        'trigger_event',
        'trigger_conditions',
        'sms_template_id',
        'delay_minutes',
        'schedule_options',
        'is_active',
        'send_to_customer',
        'send_to_staff',
        'additional_recipients',
        'created_by',
    ];

    protected $casts = [
        'trigger_conditions' => 'array',
        'schedule_options' => 'array',
        'additional_recipients' => 'array',
        'is_active' => 'boolean',
        'send_to_customer' => 'boolean',
        'send_to_staff' => 'boolean',
    ];

    /**
     * Available trigger events.
     */
    public const TRIGGER_EVENTS = [
        'ticket_created' => 'Ticket Created',
        'ticket_status_changed' => 'Ticket Status Changed',
        'appointment_created' => 'Appointment Created',
        'appointment_reminder' => 'Appointment Reminder',
        'repair_completed' => 'Repair Completed',
        'device_ready_pickup' => 'Device Ready for Pickup',
        'payment_received' => 'Payment Received',
        'payment_overdue' => 'Payment Overdue',
        'follow_up_scheduled' => 'Follow-up Scheduled',
    ];

    /**
     * SMS template relationship.
     */
    public function smsTemplate(): BelongsTo
    {
        return $this->belongsTo(SmsTemplate::class);
    }

    /**
     * Creator relationship.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get only active triggers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by trigger event.
     */
    public function scopeByEvent($query, string $event)
    {
        return $query->where('trigger_event', $event);
    }

    /**
     * Check if trigger conditions are met.
     */
    public function conditionsMet(array $data): bool
    {
        if (empty($this->trigger_conditions)) {
            return true;
        }

        foreach ($this->trigger_conditions as $field => $expectedValue) {
            if (!isset($data[$field]) || $data[$field] !== $expectedValue) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get delay in minutes for sending.
     */
    public function getDelayMinutes(): int
    {
        return $this->delay_minutes;
    }

    /**
     * Get recipients for this trigger.
     */
    public function getRecipients(Model $model): array
    {
        $recipients = [];

        // Customer phone (if model has customer relationship)
        if ($this->send_to_customer && method_exists($model, 'customer') && $model->customer?->phone) {
            $recipients[] = $model->customer->phone;
        }

        // Staff phone (if model has assigned staff)
        if ($this->send_to_staff && method_exists($model, 'assignedTo') && $model->assignedTo?->phone) {
            $recipients[] = $model->assignedTo->phone;
        }

        // Additional recipients
        if (!empty($this->additional_recipients)) {
            $recipients = array_merge($recipients, $this->additional_recipients);
        }

        return array_filter(array_unique($recipients));
    }

    /**
     * Generate message variables from model.
     */
    public function generateVariables(Model $model): array
    {
        $variables = [];

        // Common variables
        if (method_exists($model, 'customer') && $model->customer) {
            $variables['customer_name'] = $model->customer->full_name;
            $variables['customer_phone'] = $model->customer->phone;
        }

        if (isset($model->ticket_number)) {
            $variables['ticket_number'] = $model->ticket_number;
        }

        if (method_exists($model, 'device') && $model->device) {
            $variables['device'] = $model->device->brand . ' ' . $model->device->model;
        }

        if (isset($model->status)) {
            $status = $model->status;
            if ($status instanceof \BackedEnum) {
                $variables['status'] = ucwords(str_replace('_', ' ', $status->value));
            } else {
                $variables['status'] = ucwords(str_replace('_', ' ', $status));
            }
        }

        // Branch information
        if (session('current_branch')) {
            $variables['branch_name'] = session('current_branch')->name ?? 'Our Location';
            $variables['branch_phone'] = session('current_branch')->phone ?? '';
            $variables['branch_address'] = session('current_branch')->address ?? '';
        }

        $variables['current_date'] = now()->format('M j, Y');
        $variables['current_time'] = now()->format('g:i A');

        return $variables;
    }
}
