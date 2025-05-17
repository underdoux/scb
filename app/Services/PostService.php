<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Services\OpenAIService;
use App\Services\SchedulerService;
use Exception;
use Illuminate\Support\Facades\DB;

class PostService
{
    public function __construct(
        private OpenAIService $openAI,
        private SchedulerService $scheduler
    ) {}

    /**
     * Create a new post
     */
    public function createPost(array $data, User $user): Post
    {
        return DB::transaction(function () use ($data, $user) {
            $post = Post::create([
                'user_id' => $user->id,
                'content' => $data['content'],
                'hashtags' => $data['hashtags'] ?? null,
                'platform' => $data['platform'],
                'status' => 'draft',
                'gpt_prompt' => $data['gpt_prompt'] ?? null,
                'gpt_response' => $data['gpt_response'] ?? null,
            ]);

            // If scheduling is requested
            if (!empty($data['scheduled_time'])) {
                $this->scheduler->schedulePost($post, $data['scheduled_time']);
            }

            return $post;
        });
    }

    /**
     * Generate content for a post using OpenAI
     */
    public function generateContent(string $prompt, User $user): array
    {
        return $this->openAI->generateContent($prompt, $user->id);
    }

    /**
     * Generate variations of existing content
     */
    public function generateVariations(string $content, User $user, int $count = 3): array
    {
        return $this->openAI->generateVariations($content, $count, $user->id);
    }

    /**
     * Schedule a post for future publishing
     */
    public function schedulePost(Post $post, string $scheduledTime): bool
    {
        try {
            $this->scheduler->schedulePost($post, $scheduledTime);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Cancel a scheduled post
     */
    public function cancelSchedule(Post $post): bool
    {
        $schedule = $post->schedule;
        
        if (!$schedule) {
            return false;
        }

        return $this->scheduler->cancelSchedule($schedule);
    }

    /**
     * Update an existing post
     */
    public function updatePost(Post $post, array $data): Post
    {
        return DB::transaction(function () use ($post, $data) {
            $post->update([
                'content' => $data['content'] ?? $post->content,
                'hashtags' => $data['hashtags'] ?? $post->hashtags,
                'platform' => $data['platform'] ?? $post->platform,
                'gpt_prompt' => $data['gpt_prompt'] ?? $post->gpt_prompt,
                'gpt_response' => $data['gpt_response'] ?? $post->gpt_response,
            ]);

            // Handle scheduling changes
            if (isset($data['scheduled_time'])) {
                if ($post->schedule) {
                    // Cancel existing schedule if new time is empty
                    if (empty($data['scheduled_time'])) {
                        $this->cancelSchedule($post);
                    } else {
                        // Update existing schedule
                        $post->schedule->update([
                            'scheduled_time' => $data['scheduled_time'],
                            'status' => 'pending',
                            'retry_count' => 0,
                        ]);
                    }
                } elseif (!empty($data['scheduled_time'])) {
                    // Create new schedule
                    $this->scheduler->schedulePost($post, $data['scheduled_time']);
                }
            }

            return $post;
        });
    }

    /**
     * Delete a post and its associated schedule
     */
    public function deletePost(Post $post): bool
    {
        try {
            return DB::transaction(function () use ($post) {
                // Cancel schedule if exists
                if ($post->schedule) {
                    $this->cancelSchedule($post);
                }

                return $post->delete();
            });
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get posts for a user with optional filters
     */
    public function getPosts(User $user, array $filters = []): array
    {
        $query = $user->posts()->with(['schedule']);

        // Apply platform filter
        if (!empty($filters['platform'])) {
            $query->where('platform', $filters['platform']);
        }

        // Apply status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Apply date range filter
        if (!empty($filters['from_date'])) {
            $query->where('created_at', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->where('created_at', '<=', $filters['to_date']);
        }

        // Apply search filter
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('content', 'like', "%{$filters['search']}%")
                  ->orWhere('hashtags', 'like', "%{$filters['search']}%");
            });
        }

        // Apply sorting
        $sortField = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        return [
            'total' => $query->count(),
            'posts' => $query->paginate($filters['per_page'] ?? 15)
        ];
    }
}
