<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Process scheduled posts every minute
        $schedule->command('social:process-scheduled-posts')
            ->everyMinute()
            ->withoutOverlapping();

        // Check and refresh social media tokens daily
        $schedule->command('social:check-tokens')
            ->daily()
            ->withoutOverlapping();

        // Clean up old logs weekly
        $schedule->command('social:cleanup-logs')
            ->weekly()
            ->withoutOverlapping();

        // Run queue worker health check every 5 minutes
        $schedule->command('queue:check-workers')
            ->everyFiveMinutes()
            ->withoutOverlapping();

        // Prune old job batches and failed jobs daily
        $schedule->command('queue:prune-batches --hours=48')
            ->daily();
        $schedule->command('queue:prune-failed --hours=72')
            ->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
