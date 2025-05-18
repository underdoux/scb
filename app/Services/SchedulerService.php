<?php

namespace App\Services;

use App\Jobs\PublishSocialPost;
use App\Models\Post;
use App\Models\Schedule;
use App\Models\Log;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class SchedulerService
{
    /**
     * Schedule a post for publishing
     */
    public function schedulePost(Post $post, Carbon $scheduledTime): Schedule
    {
        try {
            DB::beginTransaction();

            // Create schedule
            $schedule = Schedule::create([
                'post_id' => $post->id,
                'scheduled_time' => $scheduledTime,
                'status' => 'pending',
            ]);

            // Update post status
            $post->update(['status' => 'scheduled']);

            // Log the scheduling
            Log::info(
                'Post scheduled successfully',
                [
                    'scheduled_time' => $scheduledTime->toDateTimeString(),
                    'platform' => $post->platform,
                ],
                $post->user_id,
                $post->id
            );

            DB::commit();

            return $schedule;
        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error(
                'Failed to schedule post',
                [
                    'error' => $e->getMessage(),
                    'platform' => $post->platform,
                ],
                $post->user_id,
                $post->id
            );

            throw $e;
        }
    }

    /**
     * Cancel a scheduled post
     */
    public function cancelSchedule(Schedule $schedule): bool
    {
        try {
            DB::beginTransaction();

            // Update schedule status
            $schedule->update(['status' => 'cancelled']);

            // Update post status
            $schedule->post->update(['status' => 'draft']);

            // Log the cancellation
            Log::info(
                'Schedule cancelled successfully',
                [
                    'scheduled_time' => $schedule->scheduled_time,
                    'platform' => $schedule->post->platform,
                ],
                $schedule->post->user_id,
                $schedule->post->id
            );

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error(
                'Failed to cancel schedule',
                [
                    'error' => $e->getMessage(),
                    'platform' => $schedule->post->platform,
                ],
                $schedule->post->user_id,
                $schedule->post->id
            );

            throw $e;
        }
    }

    /**
     * Process a scheduled post
     */
    public function processSchedule(Schedule $schedule): void
    {
        try {
            // Validate post exists and is in scheduled status
            if (!$schedule->post || $schedule->post->status !== 'scheduled') {
                throw new Exception('Invalid post status for scheduling');
            }

            // Dispatch job to publish post
            PublishSocialPost::dispatch($schedule->post)
                ->onQueue('social-posts');

            // Update schedule status
            $schedule->update(['status' => 'processed']);

            // Log the processing
            Log::info(
                'Schedule processed successfully',
                [
                    'scheduled_time' => $schedule->scheduled_time,
                    'platform' => $schedule->post->platform,
                ],
                $schedule->post->user_id,
                $schedule->post->id
            );

        } catch (Exception $e) {
            Log::error(
                'Failed to process schedule',
                [
                    'error' => $e->getMessage(),
                    'platform' => $schedule->post->platform ?? 'unknown',
                ],
                $schedule->post->user_id ?? null,
                $schedule->post->id ?? null
            );

            throw $e;
        }
    }

    /**
     * Get upcoming scheduled posts for a user
     */
    public function getUpcomingSchedules(int $userId, ?string $platform = null, int $limit = 10): array
    {
        $query = Schedule::with(['post'])
            ->whereHas('post', function ($query) use ($userId, $platform) {
                $query->where('user_id', $userId);
                if ($platform) {
                    $query->where('platform', $platform);
                }
            })
            ->where('status', 'pending')
            ->where('scheduled_time', '>', now())
            ->orderBy('scheduled_time')
            ->limit($limit);

        return $query->get()->map(function ($schedule) {
            return [
                'id' => $schedule->id,
                'post_id' => $schedule->post_id,
                'platform' => $schedule->post->platform,
                'content' => $schedule->post->content,
                'scheduled_time' => $schedule->scheduled_time->toDateTimeString(),
                'status' => $schedule->status,
            ];
        })->toArray();
    }

    /**
     * Get processing statistics for a user
     */
    public function getStats(int $userId): array
    {
        $stats = [
            'total_scheduled' => 0,
            'pending' => 0,
            'processed' => 0,
            'failed' => 0,
            'cancelled' => 0,
            'by_platform' => [],
        ];

        // Get schedules for user's posts
        $schedules = Schedule::whereHas('post', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get();

        // Calculate totals
        $stats['total_scheduled'] = $schedules->count();
        $stats['pending'] = $schedules->where('status', 'pending')->count();
        $stats['processed'] = $schedules->where('status', 'processed')->count();
        $stats['failed'] = $schedules->where('status', 'failed')->count();
        $stats['cancelled'] = $schedules->where('status', 'cancelled')->count();

        // Calculate by platform
        $schedules->each(function ($schedule) use (&$stats) {
            $platform = $schedule->post->platform;
            if (!isset($stats['by_platform'][$platform])) {
                $stats['by_platform'][$platform] = [
                    'total' => 0,
                    'pending' => 0,
                    'processed' => 0,
                    'failed' => 0,
                    'cancelled' => 0,
                ];
            }
            $stats['by_platform'][$platform]['total']++;
            $stats['by_platform'][$platform][$schedule->status]++;
        });

        return $stats;
    }
}
