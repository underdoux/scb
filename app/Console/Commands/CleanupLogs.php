<?php

namespace App\Console\Commands;

use App\Models\Log;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:cleanup {--days=30 : Number of days to keep logs} {--type= : Specific type of logs to clean}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old logs from the database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = $this->option('days');
        $type = $this->option('type');

        $this->info("Starting logs cleanup...");
        $this->info("Keeping logs from the last {$days} days" . ($type ? " for type: {$type}" : ""));

        try {
            DB::beginTransaction();

            $query = Log::where('created_at', '<', now()->subDays($days));

            if ($type) {
                $query->where('type', $type);
            }

            $count = $query->count();
            
            if ($count === 0) {
                $this->info("No logs found to clean up.");
                return Command::SUCCESS;
            }

            // Delete in chunks to prevent memory issues
            $query->chunkById(1000, function ($logs) {
                foreach ($logs as $log) {
                    $log->delete();
                }
            });

            DB::commit();

            $this->info("Successfully deleted {$count} logs.");

            // Create a new log entry for this cleanup
            Log::info(
                'Logs cleanup completed',
                [
                    'deleted_count' => $count,
                    'days_kept' => $days,
                    'type' => $type ?? 'all'
                ]
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error(
                'Failed to cleanup logs',
                [
                    'error' => $e->getMessage(),
                    'days' => $days,
                    'type' => $type
                ]
            );

            $this->error("Failed to cleanup logs: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
