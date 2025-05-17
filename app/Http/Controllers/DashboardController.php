<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\SocialAccount;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with statistics
     */
    public function index()
    {
        $user = Auth::user();

        // Calculate post statistics
        $totalPosts = Post::where('user_id', $user->id)->count();
        $lastWeekPosts = Post::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subWeek())
            ->count();
        $previousWeekPosts = Post::where('user_id', $user->id)
            ->whereBetween('created_at', [
                now()->subWeeks(2),
                now()->subWeek()
            ])
            ->count();

        // Calculate trend percentage
        $postsTrend = $previousWeekPosts > 0
            ? (($lastWeekPosts - $previousWeekPosts) / $previousWeekPosts) * 100
            : 0;

        // Get recent posts
        $recentPosts = Post::where('user_id', $user->id)
            ->with('schedule')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'content' => $post->content,
                    'platform' => $post->platform,
                    'status' => $post->status,
                    'created_at' => $post->created_at->diffForHumans(),
                ];
            });

        // Get recent activity logs
        $recentLogs = Log::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'type' => $log->type,
                    'message' => $log->message,
                    'created_at' => $log->created_at->diffForHumans(),
                ];
            });

        // Get upcoming scheduled posts
        $upcomingSchedules = Post::where('user_id', $user->id)
            ->where('status', 'scheduled')
            ->with('schedule')
            ->whereHas('schedule', function ($query) {
                $query->where('scheduled_time', '>', now());
            })
            ->take(5)
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'post' => [
                        'id' => $post->id,
                        'content' => $post->content,
                        'platform' => $post->platform,
                    ],
                    'scheduled_time' => $post->schedule->scheduled_time->format('Y-m-d H:i'),
                ];
            });

        // Get platform status
        $platformStatus = collect(['facebook', 'instagram', 'twitter', 'linkedin', 'tiktok', 'youtube'])
            ->map(function ($platform) use ($user) {
                $account = SocialAccount::where('user_id', $user->id)
                    ->where('platform', $platform)
                    ->first();

                return [
                    'name' => ucfirst($platform),
                    'connected' => !is_null($account),
                    'username' => $account?->platform_username,
                ];
            });

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_posts' => $totalPosts,
                'posts_trend' => round($postsTrend, 1),
                'scheduled_posts' => Post::where('user_id', $user->id)
                    ->where('status', 'scheduled')
                    ->count(),
                'next_scheduled' => Post::where('user_id', $user->id)
                    ->where('status', 'scheduled')
                    ->whereHas('schedule', function ($query) {
                        $query->where('scheduled_time', '>', now());
                    })
                    ->with('schedule')
                    ->orderBy('scheduled_time')
                    ->first()?->schedule->scheduled_time->format('Y-m-d H:i'),
                'success_rate' => $this->calculateSuccessRate($user->id),
                'connected_accounts' => SocialAccount::where('user_id', $user->id)->count(),
                'platforms' => SocialAccount::where('user_id', $user->id)
                    ->distinct('platform')
                    ->count(),
            ],
            'recentPosts' => $recentPosts,
            'recentLogs' => $recentLogs,
            'upcomingSchedules' => $upcomingSchedules,
            'platformStatus' => $platformStatus,
        ]);
    }

    /**
     * Calculate post success rate for the last 7 days
     */
    private function calculateSuccessRate(int $userId): int
    {
        $total = Post::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays(7))
            ->whereIn('status', ['published', 'failed'])
            ->count();

        if ($total === 0) {
            return 100;
        }

        $successful = Post::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays(7))
            ->where('status', 'published')
            ->count();

        return round(($successful / $total) * 100);
    }
}
