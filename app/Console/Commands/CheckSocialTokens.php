<?php

namespace App\Console\Commands;

use App\Models\SocialAccount;
use Illuminate\Console\Command;

class CheckSocialTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'social:check-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and refresh social media access tokens if needed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking social media tokens...');

        $accounts = SocialAccount::all();
        $refreshed = 0;
        $failed = 0;

        foreach ($accounts as $account) {
            try {
                $serviceClass = "App\\Services\\SocialMedia\\" . ucfirst($account->platform) . "Service";
                $service = app($serviceClass);
                
                if ($account->needsTokenRefresh()) {
                    $this->line("Refreshing token for {$account->platform} account: {$account->platform_username}");
                    
                    $service->setAccount($account);
                    $result = $service->refreshTokenIfNeeded();

                    if ($result) {
                        $this->info("✓ Token refreshed successfully for {$account->platform}");
                        $refreshed++;
                    } else {
                        $this->error("✗ Failed to refresh token for {$account->platform}");
                        $failed++;
                    }
                } else {
                    $this->line("Token is still valid for {$account->platform} account: {$account->platform_username}");
                }
            } catch (\Exception $e) {
                $this->error("Error processing {$account->platform} account: " . $e->getMessage());
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Token check completed:");
        $this->line("- Total accounts checked: " . $accounts->count());
        $this->line("- Tokens refreshed: " . $refreshed);
        $this->line("- Failed attempts: " . $failed);
    }
}
