<?php

namespace App\Console\Commands;

use App\Services\SchedulerService;
use Illuminate\Console\Command;
use App\Models\Log;
use Exception;

class ProcessScheduledPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posts:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process scheduled social media posts that are due for publishing';

    /**
     * Execute the console command.
     */
    public function handle(SchedulerService $scheduler): int
    {
        $this->info('Starting to process scheduled posts...');

        try {
            $scheduler->processScheduledPosts();
            
            $this->info('Successfully processed scheduled posts.');
            Log::info('Scheduled posts processed successfully via cron');
            
            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error('Failed to process scheduled posts: ' . $e->getMessage());
            Log::error('Failed to process scheduled posts via cron', [
                'error' => $e->getMessage()
            ]);
            
            return Command::FAILURE;
        }
    }
}
