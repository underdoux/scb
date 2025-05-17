<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    /**
     * Display analytics dashboard
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'platform' => ['nullable', 'string'],
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ]);

        $query = Post::where('user_id', Auth::id());

        // Apply date filters
        if (!empty($filters['from_date'])) {
            $query->where('created_at', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->where('created_at', '<=', $filters['to_date']);
        }

        // Apply platform filter
        if (!empty($filters['platform'])) {
            $query->where('platform', $filters['platform']);
        }

        // Get post statistics
        $stats = [
            'total_posts' => $query->count(),
            'published_posts' => $query->where('status', 'published')->count(),
            'scheduled_posts' => $query->where('status', 'scheduled')->count(),
            'failed_posts' => $query->where('status', 'failed')->count(),

            // Posts by platform
            'posts_by_platform' => Post::where('user_id', Auth::id())
                ->select('platform', DB::raw('count(*) as count'))
                ->groupBy('platform')
                ->get()
                ->pluck('count', 'platform'),

            // Posts by status
            'posts_by_status' => Post::where('user_id', Auth::id())
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status'),

            // Posts over time
            'posts_over_time' => Post::where('user_id', Auth::id())
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('count(*) as count')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(fn($item) => [
                    'date' => $item->date,
                    'count' => $item->count
                ]),

            // Success rate
            'success_rate' => $this->calculateSuccessRate(),

            // Average posts per day
            'avg_posts_per_day' => $this->calculateAveragePostsPerDay(),

            // Most active times
            'most_active_times' => $this->getMostActiveTimes(),

            // Error statistics
            'error_stats' => $this->getErrorStatistics(),
        ];

        return Inertia::render('Analytics/Index', [
            'stats' => $stats,
            'filters' => $filters,
        ]);
    }

    /**
     * Calculate post success rate
     */
    private function calculateSuccessRate(): array
    {
        $total = Post::where('user_id', Auth::id())
            ->whereIn('status', ['published', 'failed'])
            ->count();

        $published = Post::where('user_id', Auth::id())
            ->where('status', 'published')
            ->count();

        return [
            'percentage' => $total > 0 ? round(($published / $total) * 100, 2) : 0,
            'total' => $total,
            'successful' => $published,
        ];
    }

    /**
     * Calculate average posts per day
     */
    private function calculateAveragePostsPerDay(): array
    {
        $firstPost = Post::where('user_id', Auth::id())
            ->orderBy('created_at')
            ->first();

        if (!$firstPost) {
            return [
                'average' => 0,
                'days_active' => 0,
            ];
        }

        $daysActive = $firstPost->created_at->diffInDays(now()) + 1;
        $totalPosts = Post::where('user_id', Auth::id())->count();

        return [
            'average' => round($totalPosts / $daysActive, 2),
            'days_active' => $daysActive,
        ];
    }

    /**
     * Get most active posting times
     */
    private function getMostActiveTimes(): array
    {
        return Post::where('user_id', Auth::id())
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('count(*) as count')
            )
            ->groupBy('hour')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'hour' => sprintf('%02d:00', $item->hour),
                'count' => $item->count,
            ])
            ->toArray();
    }

    /**
     * Get error statistics
     */
    private function getErrorStatistics(): array
    {
        return [
            'total_errors' => Log::where('user_id', Auth::id())
                ->where('type', 'error')
                ->count(),

            'common_errors' => Log::where('user_id', Auth::id())
                ->where('type', 'error')
                ->select('message', DB::raw('count(*) as count'))
                ->groupBy('message')
                ->orderByDesc('count')
                ->limit(5)
                ->get()
                ->map(fn($item) => [
                    'message' => $item->message,
                    'count' => $item->count,
                ])
                ->toArray(),

            'errors_by_platform' => Log::where('user_id', Auth::id())
                ->where('type', 'error')
                ->whereNotNull('post_id')
                ->join('posts', 'logs.post_id', '=', 'posts.id')
                ->select('posts.platform', DB::raw('count(*) as count'))
                ->groupBy('posts.platform')
                ->get()
                ->pluck('count', 'platform')
                ->toArray(),
        ];
    }

    /**
     * Export analytics data as CSV
     */
    public function export(Request $request)
    {
        $filters = $request->validate([
            'platform' => ['nullable', 'string'],
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ]);

        $query = Post::where('user_id', Auth::id())
            ->with(['schedule', 'logs']);

        // Apply filters
        if (!empty($filters['platform'])) {
            $query->where('platform', $filters['platform']);
        }
        if (!empty($filters['from_date'])) {
            $query->where('created_at', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->where('created_at', '<=', $filters['to_date']);
        }

        $posts = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="analytics.csv"',
        ];

        $callback = function () use ($posts) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Date',
                'Platform',
                'Status',
                'Scheduled Time',
                'Published Time',
                'Error Count',
                'Content Length',
                'Has Hashtags',
                'Generated by AI',
            ]);

            foreach ($posts as $post) {
                fputcsv($file, [
                    $post->created_at->format('Y-m-d H:i:s'),
                    $post->platform,
                    $post->status,
                    $post->schedule?->scheduled_time?->format('Y-m-d H:i:s'),
                    $post->published_at?->format('Y-m-d H:i:s'),
                    $post->logs()->where('type', 'error')->count(),
                    strlen($post->content),
                    $post->hashtags ? 'Yes' : 'No',
                    $post->gpt_prompt ? 'Yes' : 'No',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
