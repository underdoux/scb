<?php

namespace App\Console\Commands;

use App\Models\Schedule;
use App\Services\SchedulerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessScheduledPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'social:process-scheduled-posts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process scheduled social media posts';

    /**
     * The scheduler service instance.
     */
    protected SchedulerService $schedulerService;

    /**
     * Create a new command instance.
     */
    public function __construct(SchedulerService $schedulerService)
    {
        parent::__construct();
        $this->schedulerService = $schedulerService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing scheduled posts...');

        try {
            // Get pending schedules that are due
            $schedules = Schedule::with(['post.user'])
                ->where('status', 'pending')
                ->where('scheduled_time', '<=', now())
                ->get();

            if ($schedules->isEmpty()) {
                $this->info('No scheduled posts to process.');
                return 0;
            }

            $this->info("Found {$schedules->count()} scheduled posts to process.");

            $processed = 0;
            $failed = 0;

            foreach ($schedules as $schedule) {
                try {
                    $this->line("Processing schedule #{$schedule->id} for post #{$schedule->post_id}...");
                    
                    // Update status to processing
                    $schedule->update(['status' => 'processing']);

                    // Process the schedule
                    $this->schedulerService->processSchedule($schedule);

                    $processed++;
                    $this->info("✓ Successfully processed schedule #{$schedule->id}");

                } catch (\Exception $e) {
                    $failed++;
                    $this->error("✗ Failed to process schedule #{$schedule->id}: " . $e->getMessage());
                    
                    Log::error('Failed to process scheduled post', [
                        'schedule_id' => $schedule->id,
                        'post_id' => $schedule->post_id,
                        'error' => $e->getMessage(),
                    ]);

                    // Increment retry count and update status
                    $schedule->incrementRetryCount();
                    
                    if ($schedule->shouldRetry()) {
                        $schedule->update(['status' => 'pending']);
                        $this->warn("Schedule #{$schedule->id} will be retried later.");
                    } else {
                        $schedule->update(['status' => 'failed']);
                        $schedule->post->update(['status' => 'failed']);
                        $this->error("Schedule #{$schedule->id} has exceeded maximum retry attempts.");
                    }
                }
            }

            $this->newLine();
            $this->info('Processing completed:');
            $this->line("- Successfully processed: {$processed}");
            $this->line("- Failed: {$failed}");

            if ($failed > 0) {
                return 1;
            }

        } catch (\Exception $e) {
            $this->error('Failed to process scheduled posts: ' . $e->getMessage());
            Log::error('Scheduled posts processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }

        return 0;
    }
}
