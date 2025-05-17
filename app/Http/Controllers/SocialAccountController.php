<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\SocialAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;

class SocialAccountController extends Controller
{
    /**
     * Display a listing of connected social accounts
     */
    public function index()
    {
        $accounts = Auth::user()->socialAccounts()
            ->get()
            ->map(function ($account) {
                return [
                    'id' => $account->id,
                    'platform' => $account->platform,
                    'platform_username' => $account->platform_username,
                    'connected_at' => $account->created_at->diffForHumans(),
                    'token_expires_at' => $account->token_expires_at?->diffForHumans(),
                ];
            });

        return Inertia::render('SocialAccounts/Index', [
            'accounts' => $accounts,
            'availablePlatforms' => [
                'facebook' => [
                    'name' => 'Facebook',
                    'connected' => Auth::user()->hasPlatformConnected('facebook'),
                ],
                'instagram' => [
                    'name' => 'Instagram',
                    'connected' => Auth::user()->hasPlatformConnected('instagram'),
                ],
                'twitter' => [
                    'name' => 'Twitter',
                    'connected' => Auth::user()->hasPlatformConnected('twitter'),
                ],
                'linkedin' => [
                    'name' => 'LinkedIn',
                    'connected' => Auth::user()->hasPlatformConnected('linkedin'),
                ],
                'tiktok' => [
                    'name' => 'TikTok',
                    'connected' => Auth::user()->hasPlatformConnected('tiktok'),
                ],
                'youtube' => [
                    'name' => 'YouTube',
                    'connected' => Auth::user()->hasPlatformConnected('youtube'),
                ],
            ],
        ]);
    }

    /**
     * Redirect to OAuth provider
     */
    public function redirect(string $platform)
    {
        if (!in_array($platform, ['facebook', 'instagram', 'twitter', 'linkedin', 'tiktok', 'youtube'])) {
            return redirect()->route('social-accounts.index')
                ->with('error', 'Invalid platform selected.');
        }

        try {
            return Socialite::driver($platform)
                ->scopes($this->getPlatformScopes($platform))
                ->redirect();
        } catch (\Exception $e) {
            Log::error(
                "Failed to redirect to {$platform} OAuth",
                ['error' => $e->getMessage()],
                Auth::id()
            );

            return redirect()->route('social-accounts.index')
                ->with('error', "Failed to connect to {$platform}.");
        }
    }

    /**
     * Handle OAuth callback
     */
    public function callback(string $platform)
    {
        try {
            $socialUser = Socialite::driver($platform)->user();

            $account = SocialAccount::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'platform' => $platform,
                    'platform_user_id' => $socialUser->getId(),
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

            Log::success(
                "{$platform} account connected successfully",
                ['platform_username' => $account->platform_username],
                Auth::id()
            );

            return redirect()->route('social-accounts.index')
                ->with('success', "{$platform} account connected successfully.");
        } catch (\Exception $e) {
            Log::error(
                "Failed to connect {$platform} account",
                ['error' => $e->getMessage()],
                Auth::id()
            );

            return redirect()->route('social-accounts.index')
                ->with('error', "Failed to connect {$platform} account.");
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
                ->first();

            if (!$account) {
                return redirect()->route('social-accounts.index')
                    ->with('error', "{$platform} account not found.");
            }

            // Attempt to revoke access token if platform supports it
            $this->revokeAccessToken($platform, $account);

            $account->delete();

            Log::info(
                "{$platform} account disconnected successfully",
                ['platform_username' => $account->platform_username],
                Auth::id()
            );

            return redirect()->route('social-accounts.index')
                ->with('success', "{$platform} account disconnected successfully.");
        } catch (\Exception $e) {
            Log::error(
                "Failed to disconnect {$platform} account",
                ['error' => $e->getMessage()],
                Auth::id()
            );

            return redirect()->route('social-accounts.index')
                ->with('error', "Failed to disconnect {$platform} account.");
        }
    }

    /**
     * Get required scopes for each platform
     */
    private function getPlatformScopes(string $platform): array
    {
        return match($platform) {
            'facebook' => ['pages_show_list', 'pages_read_engagement', 'pages_manage_posts'],
            'instagram' => ['instagram_basic', 'instagram_content_publish'],
            'twitter' => ['tweet.read', 'tweet.write', 'users.read'],
            'linkedin' => ['r_liteprofile', 'w_member_social'],
            'tiktok' => ['user.info.basic', 'video.publish'],
            'youtube' => ['youtube.upload', 'youtube.readonly'],
            default => [],
        };
    }

    /**
     * Attempt to revoke access token for platforms that support it
     */
    private function revokeAccessToken(string $platform, SocialAccount $account): void
    {
        try {
            switch ($platform) {
                case 'facebook':
                case 'instagram':
                    Http::delete("https://graph.facebook.com/v12.0/me/permissions", [
                        'access_token' => $account->access_token,
                    ]);
                    break;

                case 'twitter':
                    Http::post('https://api.twitter.com/2/oauth2/revoke', [
                        'token' => $account->access_token,
                        'client_id' => config('services.twitter.client_id'),
                    ]);
                    break;

                // Add other platform-specific token revocation logic here
            }
        } catch (\Exception $e) {
            Log::warning(
                "Failed to revoke {$platform} access token",
                ['error' => $e->getMessage()],
                Auth::id()
            );
        }
    }
}
