<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\ProcessSmsCampaign;
use App\Models\SmsCampaign;
use Illuminate\Console\Command;
use Exception;

class ProcessScheduledCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sms:process-scheduled {--limit=10 : Maximum number of campaigns to process}';

    /**
     * The console command description.
     */
    protected $description = 'Process scheduled SMS campaigns that are due to be sent';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        $campaigns = SmsCampaign::scheduled()
            ->where('scheduled_at', '<=', now())
            ->take($limit)
            ->get();

        if ($campaigns->isEmpty()) {
            $this->info('No scheduled campaigns found to process.');
            return Command::SUCCESS;
        }

        $this->info("Found {$campaigns->count()} campaign(s) to process.");

        foreach ($campaigns as $campaign) {
            try {
                $this->line("Processing campaign: {$campaign->name}");

                ProcessSmsCampaign::dispatch($campaign);

                $this->info("✓ Campaign '{$campaign->name}' dispatched for processing.");
            } catch (Exception $e) {
                $this->error("✗ Failed to process campaign '{$campaign->name}': {$e->getMessage()}");
            }
        }

        return Command::SUCCESS;
    }
}
