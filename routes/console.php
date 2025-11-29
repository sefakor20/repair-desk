<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule SMS retry every minute
Schedule::command('sms:retry-failed')->everyMinute()->withoutOverlapping();

// Schedule SMS campaign processing every minute
Schedule::command('sms:process-scheduled')->everyMinute()->withoutOverlapping();
