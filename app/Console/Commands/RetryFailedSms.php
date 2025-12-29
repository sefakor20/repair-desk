<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\SmsDeliveryLog;
use App\Services\SmsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RetryFailedSms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:retry-failed {--limit=50 : Maximum number of messages to retry}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry failed SMS messages that are scheduled for retry';

    /**
     * Execute the console command.
     */
    public function handle(SmsService $smsService): int
    {
        $limit = (int) $this->option('limit');

        // Find failed messages that are ready for retry
        // Check if retry columns exist before using them
        $hasRetryColumns = Schema::hasColumns('sms_delivery_logs', ['retry_count', 'max_retries', 'next_retry_at']);

        if (!$hasRetryColumns) {
            $this->warn('SMS retry columns are missing. Please run migrations first.');
            $this->info('Required migration: 2025_11_27_210243_add_cost_and_retry_fields_to_sms_delivery_logs_table.php');
            return self::FAILURE;
        }

        $logs = SmsDeliveryLog::where('status', 'failed')
            ->where('retry_count', '<', DB::raw('max_retries'))
            ->where(function ($query): void {
                $query->whereNull('next_retry_at')
                    ->orWhere('next_retry_at', '<=', now());
            })
            ->orderBy('created_at')
            ->limit($limit)
            ->get();

        if ($logs->isEmpty()) {
            $this->info('No failed SMS messages to retry.');

            return self::SUCCESS;
        }

        $this->info("Found {$logs->count()} failed SMS message(s) to retry.");

        $successCount = 0;
        $failedCount = 0;

        foreach ($logs as $log) {
            $attempt = $log->retry_count + 1;
            $this->line("Retrying SMS to {$log->phone} (Attempt {$attempt}/{$log->max_retries})...");

            $success = $smsService->retrySms($log);

            if ($success) {
                $successCount++;
                $this->info("✓ Successfully retried SMS to {$log->phone}");
            } else {
                $failedCount++;
                $this->error("✗ Failed to retry SMS to {$log->phone}");
            }
        }

        $this->newLine();
        $this->info("Retry complete: {$successCount} succeeded, {$failedCount} failed.");

        return self::SUCCESS;
    }
}
