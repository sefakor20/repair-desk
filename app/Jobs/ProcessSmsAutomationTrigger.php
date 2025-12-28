<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\SmsAutomationTrigger;
use App\Services\SmsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Exception;

class ProcessSmsAutomationTrigger implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public SmsAutomationTrigger $trigger,
        public Model $model,
        public array $eventData = [],
    ) {}

    public function handle(): void
    {
        try {
            // Check if trigger is still active
            if (!$this->trigger->is_active) {
                Log::info('SMS automation trigger is inactive', ['trigger_id' => $this->trigger->id]);
                return;
            }

            // Check if conditions are met
            if (!$this->trigger->conditionsMet($this->eventData)) {
                Log::info('SMS automation trigger conditions not met', [
                    'trigger_id' => $this->trigger->id,
                    'conditions' => $this->trigger->trigger_conditions,
                    'data' => $this->eventData,
                ]);
                return;
            }

            // Get recipients
            $recipients = $this->trigger->getRecipients($this->model);
            if ($recipients === []) {
                Log::warning('No recipients found for SMS automation trigger', [
                    'trigger_id' => $this->trigger->id,
                    'model_id' => $this->model->id,
                ]);
                return;
            }

            // Generate message variables
            $variables = $this->trigger->generateVariables($this->model);

            // Render message
            $message = $this->trigger->smsTemplate->render($variables);

            // Send SMS to each recipient
            $smsService = new SmsService();
            foreach ($recipients as $recipient) {
                $smsService->send(
                    message: $message,
                    notificationType: 'automation_trigger',
                    phoneNumber: $recipient,
                    notificationId: $this->model->id,
                );
            }

            Log::info('SMS automation trigger processed successfully', [
                'trigger_id' => $this->trigger->id,
                'model_id' => $this->model->id,
                'recipients_count' => count($recipients),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to process SMS automation trigger', [
                'trigger_id' => $this->trigger->id,
                'model_id' => $this->model->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
