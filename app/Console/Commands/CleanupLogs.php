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
    protected $signature = 'social:cleanup-logs {--days=30 : Number of days to keep logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old log entries from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $this->info("Cleaning up logs older than {$days} days...");

        try {
            // Begin transaction
            DB::beginTransaction();

            // Get count of logs to be deleted
            $count = Log::where('created_at', '<', now()->subDays($days))->count();

            // Delete old logs
            Log::where('created_at', '<', now()->subDays($days))->delete();

            // Commit transaction
            DB::commit();

            $this->info("Successfully deleted {$count} log entries.");
            
            // Get remaining logs count
            $remaining = Log::count();
            $this->line("Remaining logs in database: {$remaining}");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Failed to cleanup logs: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
