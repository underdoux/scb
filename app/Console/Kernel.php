<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\ProcessScheduledPosts::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Process scheduled posts every minute
        $schedule->command('posts:process-scheduled')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground();

        // Clean up old logs every week
        $schedule->command('logs:cleanup')
            ->weekly()
            ->sundays()
            ->at('00:00')
            ->runInBackground();

        // Prune old failed jobs every day
        $schedule->command('queue:prune-failed')
            ->daily()
            ->runInBackground();
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
