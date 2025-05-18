<?php

namespace App\Jobs;

use App\Models\Post;
use App\Models\Log;
use App\Services\SocialMedia\BaseSocialMediaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class PublishSocialPost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = [30, 60, 120];

    /**
     * The post instance.
     *
     * @var \App\Models\Post
     */
    protected $post;

    /**
     * Create a new job instance.
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Get the appropriate social media service
            $serviceClass = "App\\Services\\SocialMedia\\" . ucfirst($this->post->platform) . "Service";
            
            if (!class_exists($serviceClass)) {
                throw new \Exception("Social media service not found for platform: {$this->post->platform}");
            }

            /** @var BaseSocialMediaService $service */
            $service = App::make($serviceClass);

            // Get the user's social account for this platform
            $socialAccount = $this->post->user->getSocialAccount($this->post->platform);
            
            if (!$socialAccount) {
                throw new \Exception("Social account not found for platform: {$this->post->platform}");
            }

            // Set the account and publish
            $result = $service->setAccount($socialAccount)->publish($this->post);

            if (!$result['success']) {
                throw new \Exception($result['message'] ?? 'Failed to publish post');
            }

            // Update post status
            $this->post->update([
                'status' => 'published',
                'published_at' => now(),
            ]);

            // Log success
            Log::success(
                'Post published successfully',
                [
                    'platform' => $this->post->platform,
                    'post_url' => $result['data']['post_url'] ?? null,
                ],
                $this->post->user_id,
                $this->post->id
            );

        } catch (\Exception $e) {
            // Log error
            Log::error(
                'Failed to publish post',
                [
                    'error' => $e->getMessage(),
                    'platform' => $this->post->platform,
                ],
                $this->post->user_id,
                $this->post->id
            );

            // If we're out of retries, mark the post as failed
            if ($this->attempts() >= $this->tries) {
                $this->post->update(['status' => 'failed']);
            }

            throw $e;
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<string>
     */
    public function tags(): array
    {
        return [
            'social-post',
            "platform:{$this->post->platform}",
            "user:{$this->post->user_id}",
            "post:{$this->post->id}",
        ];
    }

    /**
     * The job failed to process.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error(
            'Post publishing job failed',
            [
                'error' => $exception->getMessage(),
                'platform' => $this->post->platform,
            ],
            $this->post->user_id,
            $this->post->id
        );

        $this->post->update(['status' => 'failed']);
    }
}
