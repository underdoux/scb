<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class SocialAuthController extends Controller
{
    /**
     * Redirect to provider for authentication
     */
    public function redirect(string $platform)
    {
        try {
            $scopes = $this->getPlatformScopes($platform);
            
            return Socialite::driver($this->getDriver($platform))
                ->scopes($scopes)
                ->redirect();

        } catch (Exception $e) {
            Log::error(
                'Failed to redirect to social platform',
                [
                    'platform' => $platform,
                    'error' => $e->getMessage(),
                ],
                Auth::id()
            );

            return redirect()->route('social-accounts.index')
                ->with('error', 'Failed to connect to ' . ucfirst($platform));
        }
    }

    /**
     * Handle callback from provider
     */
    public function callback(string $platform)
    {
        try {
            $socialUser = Socialite::driver($this->getDriver($platform))->user();

            // Create or update social account
            $account = SocialAccount::updateOrCreate(
                [
                    'platform' => $platform,
                    'platform_user_id' => $socialUser->getId(),
                    'user_id' => Auth::id(),
                ],
                [
                    'platform_username' => $socialUser->getNickname() ?? $socialUser->getName(),
                    'access_token' => $socialUser->token,
                    'refresh_token' => $socialUser->refreshToken,
                    'token_expires_at' => isset($socialUser->expiresIn) 
                        ? now()->addSeconds($socialUser->expiresIn)
                        : null,
                ]
            );

            // Log success
            Log::info(
                'Social account connected successfully',
                [
                    'platform' => $platform,
                    'username' => $account->platform_username,
                ],
                Auth::id()
            );

            return redirect()->route('social-accounts.index')
                ->with('success', ucfirst($platform) . ' account connected successfully');

        } catch (Exception $e) {
            Log::error(
                'Failed to connect social account',
                [
                    'platform' => $platform,
                    'error' => $e->getMessage(),
                ],
                Auth::id()
            );

            return redirect()->route('social-accounts.index')
                ->with('error', 'Failed to connect ' . ucfirst($platform) . ' account: ' . $e->getMessage());
        }
    }

    /**
     * Disconnect a social media account
     */
    public function disconnect(string $platform)
    {
        try {
            $account = Auth::user()->socialAccounts()
                ->where('platform', $platform)
                ->firstOrFail();

            // Revoke access token if possible
            $this->revokeAccess($platform, $account);

            // Delete the account
            $account->delete();

            // Log success
            Log::info(
                'Social account disconnected successfully',
                [
                    'platform' => $platform,
                    'username' => $account->platform_username,
                ],
                Auth::id()
            );

            return redirect()->route('social-accounts.index')
                ->with('success', ucfirst($platform) . ' account disconnected successfully');

        } catch (Exception $e) {
            Log::error(
                'Failed to disconnect social account',
                [
                    'platform' => $platform,
                    'error' => $e->getMessage(),
                ],
                Auth::id()
            );

            return redirect()->route('social-accounts.index')
                ->with('error', 'Failed to disconnect ' . ucfirst($platform) . ' account');
        }
    }

    /**
     * Get the appropriate driver name for the platform
     */
    protected function getDriver(string $platform): string
    {
        return match ($platform) {
            'youtube' => 'google',
            default => $platform,
        };
    }

    /**
     * Get the required scopes for each platform
     */
    protected function getPlatformScopes(string $platform): array
    {
        return match ($platform) {
            'facebook' => [
                'public_profile',
                'email',
                'pages_show_list',
                'pages_read_engagement',
                'pages_manage_posts',
                'publish_to_groups',
            ],
            'twitter' => [
                'tweet.read',
                'tweet.write',
                'users.read',
            ],
            'instagram' => [
                'basic',
                'publish_media',
                'pages_show_list',
            ],
            'linkedin' => [
                'r_liteprofile',
                'r_emailaddress',
                'w_member_social',
            ],
            'tiktok' => [
                'user.info.basic',
                'video.publish',
            ],
            'youtube' => [
                'https://www.googleapis.com/auth/youtube',
                'https://www.googleapis.com/auth/youtube.upload',
            ],
            default => [],
        };
    }

    /**
     * Revoke access token for the platform
     */
    protected function revokeAccess(string $platform, SocialAccount $account): void
    {
        try {
            $service = app('social-media')->platform($platform);
            $service->setAccount($account)->revokeToken();
        } catch (Exception $e) {
            // Log but don't throw - we still want to delete the account
            Log::warning(
                'Failed to revoke platform access token',
                [
                    'platform' => $platform,
                    'error' => $e->getMessage(),
                ],
                Auth::id()
            );
        }
    }
}
