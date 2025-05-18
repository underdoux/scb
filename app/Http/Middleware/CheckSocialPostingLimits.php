<?php

namespace App\Http\Middleware;

use App\Models\Post;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSocialPostingLimits
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Check daily posting limit
        $maxPostsPerDay = config('social.limits.max_posts_per_day', 50);
        $todayPosts = Post::where('user_id', $user->id)
            ->whereDate('created_at', Carbon::today())
            ->count();

        if ($todayPosts >= $maxPostsPerDay) {
            return redirect()->route('posts.index')
                ->with('error', "You've reached your daily posting limit of {$maxPostsPerDay} posts.");
        }

        // Check scheduled posts limit
        $maxScheduledPosts = config('social.limits.max_scheduled_posts', 100);
        $scheduledPosts = Post::where('user_id', $user->id)
            ->where('status', 'scheduled')
            ->count();

        if ($scheduledPosts >= $maxScheduledPosts) {
            return redirect()->route('posts.index')
                ->with('error', "You've reached your maximum limit of {$maxScheduledPosts} scheduled posts.");
        }

        // Check platform-specific limits
        $platform = $request->route('platform') ?? $request->input('platform');
        if ($platform) {
            $platformLimits = config("social.limits.platforms.{$platform}", [
                'posts_per_day' => 20,
                'scheduled_posts' => 50,
            ]);

            // Check platform daily limit
            $platformTodayPosts = Post::where('user_id', $user->id)
                ->where('platform', $platform)
                ->whereDate('created_at', Carbon::today())
                ->count();

            if ($platformTodayPosts >= $platformLimits['posts_per_day']) {
                return redirect()->route('posts.index')
                    ->with('error', "You've reached your daily posting limit of {$platformLimits['posts_per_day']} posts for " . ucfirst($platform));
            }

            // Check platform scheduled posts limit
            $platformScheduledPosts = Post::where('user_id', $user->id)
                ->where('platform', $platform)
                ->where('status', 'scheduled')
                ->count();

            if ($platformScheduledPosts >= $platformLimits['scheduled_posts']) {
                return redirect()->route('posts.index')
                    ->with('error', "You've reached your maximum limit of {$platformLimits['scheduled_posts']} scheduled posts for " . ucfirst($platform));
            }
        }

        // Check file upload limits if request has files
        if ($request->hasFile('media')) {
            $maxFileSize = config('social.limits.max_file_size', 100 * 1024 * 1024); // 100MB default
            $supportedTypes = config('social.limits.supported_file_types', [
                'image' => ['jpg', 'jpeg', 'png', 'gif'],
                'video' => ['mp4', 'mov', 'avi'],
                'document' => ['pdf', 'doc', 'docx'],
            ]);

            $files = is_array($request->file('media')) 
                ? $request->file('media') 
                : [$request->file('media')];

            foreach ($files as $file) {
                // Check file size
                if ($file->getSize() > $maxFileSize) {
                    return redirect()->back()
                        ->with('error', "File '{$file->getClientOriginalName()}' exceeds the maximum file size limit of " . 
                            number_format($maxFileSize / 1024 / 1024, 0) . "MB");
                }

                // Check file type
                $extension = strtolower($file->getClientOriginalExtension());
                $validExtensions = array_merge(...array_values($supportedTypes));
                
                if (!in_array($extension, $validExtensions)) {
                    return redirect()->back()
                        ->with('error', "File type '{$extension}' is not supported");
                }
            }
        }

        return $next($request);
    }
}
