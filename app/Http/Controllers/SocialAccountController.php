<?php

namespace App\Http\Controllers;

use App\Models\SocialAccount;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SocialAccountController extends Controller
{
    /**
     * Display a listing of the user's social media accounts.
     */
    public function index()
    {
        $accounts = Auth::user()->socialAccounts()
            ->orderBy('platform')
            ->get()
            ->map(function ($account) {
                return [
                    'id' => $account->id,
                    'platform' => $account->platform,
                    'platform_username' => $account->platform_username,
                    'connected_at' => $account->created_at,
                    'updated_at' => $account->updated_at,
                    'token_expires_at' => $account->token_expires_at,
                    'status' => $this->getAccountStatus($account),
                ];
            });

        return Inertia::render('SocialAccounts/Index', [
            'accounts' => $accounts,
            'platforms' => [
                'facebook' => [
                    'name' => 'Facebook',
                    'description' => 'Share posts to your Facebook pages',
                    'features' => ['Text posts', 'Images', 'Videos', 'Links'],
                    'limits' => [
                        'text' => '63,206 characters',
                        'images' => 'Up to 10 images per post',
                        'video' => 'Up to 240 minutes',
                    ],
                ],
                'twitter' => [
                    'name' => 'Twitter',
                    'description' => 'Share tweets to your Twitter profile',
                    'features' => ['Text posts', 'Images', 'Videos', 'Links'],
                    'limits' => [
                        'text' => '280 characters',
                        'images' => 'Up to 4 images per tweet',
                        'video' => 'Up to 140 seconds',
                    ],
                ],
                'instagram' => [
                    'name' => 'Instagram',
                    'description' => 'Share photos and videos to Instagram',
                    'features' => ['Images', 'Videos', 'Stories', 'Reels'],
                    'limits' => [
                        'caption' => '2,200 characters',
                        'images' => 'Up to 10 images per post',
                        'video' => 'Up to 60 minutes',
                    ],
                ],
                'linkedin' => [
                    'name' => 'LinkedIn',
                    'description' => 'Share professional updates to LinkedIn',
                    'features' => ['Text posts', 'Images', 'Videos', 'Articles'],
                    'limits' => [
                        'text' => '3,000 characters',
                        'images' => 'Up to 9 images per post',
                        'video' => 'Up to 10 minutes',
                    ],
                ],
                'tiktok' => [
                    'name' => 'TikTok',
                    'description' => 'Share short-form videos to TikTok',
                    'features' => ['Videos', 'Duets', 'Stitches'],
                    'limits' => [
                        'caption' => '2,200 characters',
                        'video' => 'Up to 10 minutes',
                    ],
                ],
                'youtube' => [
                    'name' => 'YouTube',
                    'description' => 'Share videos to YouTube',
                    'features' => ['Videos', 'Shorts', 'Live streams'],
                    'limits' => [
                        'title' => '100 characters',
                        'description' => '5,000 characters',
                        'video' => 'No length limit',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Get the current status of a social media account
     */
    protected function getAccountStatus(SocialAccount $account): array
    {
        $status = [
            'connected' => true,
            'expires_soon' => false,
            'expired' => false,
            'message' => 'Connected',
            'class' => 'success',
        ];

        // Check if token is expired or expiring soon
        if ($account->token_expires_at) {
            $now = now();
            $expiresAt = $account->token_expires_at;

            if ($expiresAt->isPast()) {
                $status['expired'] = true;
                $status['message'] = 'Token expired';
                $status['class'] = 'error';
            } elseif ($expiresAt->diffInHours($now) < 24) {
                $status['expires_soon'] = true;
                $status['message'] = 'Token expires soon';
                $status['class'] = 'warning';
            }
        }

        // Check last successful post
        $lastPost = $account->user->posts()
            ->where('platform', $account->platform)
            ->where('status', 'published')
            ->latest('published_at')
            ->first();

        if ($lastPost) {
            $status['last_post'] = [
                'date' => $lastPost->published_at,
                'success' => true,
            ];
        }

        // Check for recent errors
        $recentError = Log::where('user_id', $account->user_id)
            ->where('type', 'error')
            ->where('context->platform', $account->platform)
            ->latest()
            ->first();

        if ($recentError && $recentError->created_at->diffInHours(now()) < 24) {
            $status['has_error'] = true;
            $status['error_message'] = $recentError->message;
        }

        return $status;
    }

    /**
     * Display the specified social media account.
     */
    public function show(SocialAccount $account)
    {
        $this->authorize('view', $account);

        // Get account details from the platform
        try {
            $service = app('social-media')->platform($account->platform);
            $platformDetails = $service->setAccount($account)->getAccountDetails();

            return response()->json([
                'account' => $account,
                'platform_details' => $platformDetails,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch account details: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified social media account settings.
     */
    public function update(Request $request, SocialAccount $account)
    {
        $this->authorize('update', $account);

        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        try {
            $account->update([
                'settings' => array_merge($account->settings ?? [], $validated['settings']),
            ]);

            Log::info(
                'Account settings updated successfully',
                [
                    'platform' => $account->platform,
                    'settings' => $validated['settings'],
                ],
                Auth::id()
            );

            return back()->with('success', 'Account settings updated successfully');
        } catch (\Exception $e) {
            Log::error(
                'Failed to update account settings',
                [
                    'platform' => $account->platform,
                    'error' => $e->getMessage(),
                ],
                Auth::id()
            );

            return back()->with('error', 'Failed to update account settings');
        }
    }
}
