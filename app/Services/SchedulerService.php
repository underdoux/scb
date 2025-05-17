<?php

namespace App\Services;

use App\Models\Log;
use App\Models\Post;
use App\Models\Schedule;
use App\Services\SocialMedia\BaseSocialMediaService;
use Exception;
use Illuminate\Support\Facades\App;

class SchedulerService
{
    /**
     * Schedule a post for future publishing
     */
    public function schedulePost(Post $post, string $scheduledTime): Schedule
    {
        $schedule = Schedule::create([
            'post_id' => $post->id,
            'scheduled_time' => $scheduledTime,
            'status' => 'pending'
        ]);

        $post->update(['status' => 'scheduled']);

        Log::info(
            'Post scheduled successfully',
            [
                'scheduled_time' => $scheduledTime,
                'platform' => $post->platform
            ],
            $post->user_id,
            $post->id
        );

        return $schedule;
    }

    /**
     * Process scheduled posts that are due
     */
    public function processScheduledPosts(): void
    {
        $schedules = Schedule::with(['post.user'])
            ->where('status', 'pending')
            ->where('scheduled_time', '<=', now())
            ->get();

        foreach ($schedules as $schedule) {
            $this->processSchedule($schedule);
        }
    }

    /**
     * Process a single schedule
     */
    private function processSchedule(Schedule $schedule): void
    {
        $post = $schedule->post;

        if (!$post) {
            $schedule->update(['status' => 'failed']);
            Log::error('Post not found for schedule', ['schedule_id' => $schedule->id]);
            return;
        }

        try {
            $schedule->update(['status' => 'processing']);

            // Get the appropriate social media service
            $service = $this->getSocialMediaService($post->platform);
            
            if (!$service) {
                throw new Exception("Social media service not found for platform: {$post->platform}");
            }

            // Get the user's social account for this platform
            $socialAccount = $post->user->getSocialAccount($post->platform);
            
            if (!$socialAccount) {
                throw new Exception("Social account not found for platform: {$post->platform}");
            }

            // Set the account and publish
            $result = $service->setAccount($socialAccount)->publish($post);

            if (!$result['success']) {
                throw new Exception($result['message']);
            }

            $schedule->update(['status' => 'completed']);
            $post->update(['status' => 'published', 'published_at' => now()]);

            Log::success(
                'Post published successfully',
                [
                    'platform' => $post->platform,
                    'scheduled_time' => $schedule->scheduled_time
                ],
                $post->user_id,
                $post->id
            );

        } catch (Exception $e) {
            $schedule->incrementRetryCount();
            
            if ($schedule->shouldRetry()) {
                $schedule->update(['status' => 'pending']);
                Log::warning(
                    'Post publishing failed, will retry',
                    [
                        'error' => $e->getMessage(),
                        'retry_count' => $schedule->retry_count,
                        'platform' => $post->platform
                    ],
                    $post->user_id,
                    $post->id
                );
            } else {
                $schedule->update(['status' => 'failed']);
                $post->update(['status' => 'failed']);
                Log::error(
                    'Post publishing failed permanently',
                    [
                        'error' => $e->getMessage(),
                        'platform' => $post->platform
                    ],
                    $post->user_id,
                    $post->id
                );
            }
        }
    }

    /**
     * Get the appropriate social media service for a platform
     */
    private function getSocialMediaService(string $platform): ?BaseSocialMediaService
    {
        $serviceClass = "App\\Services\\SocialMedia\\" . ucfirst($platform) . "Service";
        
        return class_exists($serviceClass) ? App::make($serviceClass) : null;
    }

    /**
     * Cancel a scheduled post
     */
    public function cancelSchedule(Schedule $schedule): bool
    {
        try {
            $post = $schedule->post;
            
            $schedule->delete();
            $post->update(['status' => 'draft']);

            Log::info(
                'Schedule cancelled successfully',
                [
                    'scheduled_time' => $schedule->scheduled_time,
                    'platform' => $post->platform
                ],
                $post->user_id,
                $post->id
            );

            return true;
        } catch (Exception $e) {
            Log::error(
                'Failed to cancel schedule',
                [
                    'error' => $e->getMessage(),
                    'schedule_id' => $schedule->id
                ],
                $post->user_id ?? null,
                $post->id ?? null
            );

            return false;
        }
    }
}
