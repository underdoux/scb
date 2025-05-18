<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CheckQueueWorkers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:check-workers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the health of queue workers and restart if needed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking queue workers health...');

        try {
            // Check if any jobs are stuck
            $stuckJobs = $this->checkStuckJobs();
            
            // Check worker memory usage
            $highMemoryWorkers = $this->checkWorkerMemory();
            
            // Check worker uptime
            $longRunningWorkers = $this->checkWorkerUptime();

            if ($stuckJobs || $highMemoryWorkers || $longRunningWorkers) {
                $this->restartSupervisorWorkers();
                $this->info('Queue workers have been restarted.');
                
                Log::warning('Queue workers were restarted due to health check failure', [
                    'stuck_jobs' => $stuckJobs,
                    'high_memory_workers' => $highMemoryWorkers,
                    'long_running_workers' => $longRunningWorkers,
                ]);
            } else {
                $this->info('All queue workers are healthy.');
            }

            // Update last health check timestamp
            Cache::put('queue_workers_last_health_check', now(), now()->addHours(24));

        } catch (\Exception $e) {
            $this->error('Failed to check queue workers: ' . $e->getMessage());
            Log::error('Queue worker health check failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }

        return 0;
    }

    /**
     * Check for jobs that have been processing for too long
     */
    private function checkStuckJobs(): bool
    {
        $threshold = now()->subHours(1);
        
        $stuckJobs = \DB::table('jobs')
            ->where('reserved_at', '<=', $threshold->timestamp)
            ->where('attempts', '>', 0)
            ->count();

        if ($stuckJobs > 0) {
            $this->warn("Found {$stuckJobs} stuck jobs.");
            return true;
        }

        return false;
    }

    /**
     * Check worker memory usage through supervisor
     */
    private function checkWorkerMemory(): bool
    {
        // Memory threshold (500MB)
        $memoryThreshold = 500 * 1024 * 1024;
        
        try {
            $processes = $this->getSupervisorProcessInfo();
            
            foreach ($processes as $process) {
                if ($process['memory'] > $memoryThreshold) {
                    $this->warn("Worker {$process['pid']} is using excessive memory: {$process['memory']} bytes");
                    return true;
                }
            }
        } catch (\Exception $e) {
            $this->warn('Could not check worker memory: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Check how long workers have been running
     */
    private function checkWorkerUptime(): bool
    {
        // Uptime threshold (24 hours)
        $uptimeThreshold = 24 * 60 * 60;
        
        try {
            $processes = $this->getSupervisorProcessInfo();
            
            foreach ($processes as $process) {
                if ($process['uptime'] > $uptimeThreshold) {
                    $this->warn("Worker {$process['pid']} has been running for too long: {$process['uptime']} seconds");
                    return true;
                }
            }
        } catch (\Exception $e) {
            $this->warn('Could not check worker uptime: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Get supervisor process information
     */
    private function getSupervisorProcessInfo(): array
    {
        // This is a placeholder. In production, you would implement
        // actual supervisor XML-RPC calls or use process information
        // from your process manager
        return [];
    }

    /**
     * Restart supervisor workers
     */
    private function restartSupervisorWorkers(): void
    {
        try {
            // In production, implement actual supervisor control
            // through XML-RPC or process manager commands
            $this->info('Restarting workers through supervisor...');
        } catch (\Exception $e) {
            $this->error('Failed to restart workers: ' . $e->getMessage());
            throw $e;
        }
    }
}
