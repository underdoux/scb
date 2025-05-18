<?php

namespace App\Services\SocialMedia;

use App\Models\Post;
use App\Models\SocialAccount;
use App\Models\Log;
use App\Notifications\PostStatusNotification;
use Carbon\Carbon;
use Exception;

abstract class BaseSocialMediaService
{
    /**
     * The social account instance.
     */
    protected ?SocialAccount $account = null;

    /**
     * Set the social account for operations.
     */
    public function setAccount(SocialAccount $account): self
    {
        $this->account = $account;
        return $this;
    }

    /**
     * Get the account details from the platform.
     */
    abstract public function getAccountDetails(): array;

    /**
     * Publish a post to the platform.
     */
    abstract public function publish(Post $post): array;

    /**
     * Check if the access token needs to be refreshed.
     */
    public function needsTokenRefresh(): bool
    {
        if (!$this->account) {
            throw new Exception('No social account set');
        }

        // If there's no expiration time, assume token is still valid
        if (!$this->account->token_expires_at) {
            return false;
        }

        // Check if token expires within the next hour
        return $this->account->token_expires_at->subHour()->isPast();
    }

    /**
     * Refresh the access token if needed.
     */
    public function refreshTokenIfNeeded(): bool
    {
        if (!$this->account) {
            throw new Exception('No social account set');
        }

        try {
            if ($this->needsTokenRefresh()) {
                $result = $this->refreshToken();
                
                if ($result['success']) {
                    $this->account->update([
                        'access_token' => $result['data']['access_token'],
                        'refresh_token' => $result['data']['refresh_token'] ?? $this->account->refresh_token,
                        'token_expires_at' => isset($result['data']['expires_in']) 
                            ? Carbon::now()->addSeconds($result['data']['expires_in'])
                            : null,
                    ]);

                    Log::info(
                        'Token refreshed successfully',
                        [
                            'platform' => $this->account->platform,
                            'expires_at' => $this->account->token_expires_at,
                        ],
                        $this->account->user_id
                    );

                    return true;
                }
            }
        } catch (Exception $e) {
            Log::error(
                'Failed to refresh token',
                [
                    'platform' => $this->account->platform,
                    'error' => $e->getMessage(),
                ],
                $this->account->user_id
            );

            throw $e;
        }

        return false;
    }

    /**
     * Refresh the access token.
     */
    abstract protected function refreshToken(): array;

    /**
     * Validate post content for the platform.
     */
    abstract protected function validateContent(Post $post): void;

    /**
     * Format post content for the platform.
     */
    abstract protected function formatContent(Post $post): array;

    /**
     * Handle successful post publishing.
     */
    protected function handleSuccess(Post $post, array $data): void
    {
        // Update post status
        $post->update([
            'status' => 'published',
            'published_at' => now(),
            'platform_post_id' => $data['post_id'] ?? null,
        ]);

        // Log success
        Log::success(
            'Post published successfully',
            [
                'platform' => $post->platform,
                'post_url' => $data['post_url'] ?? null,
            ],
            $post->user_id,
            $post->id
        );

        // Notify user
        $post->user->notify(new PostStatusNotification(
            $post,
            'published',
            'Your post has been successfully published!',
            $data
        ));
    }

    /**
     * Handle post publishing failure.
     */
    protected function handleError(Post $post, Exception $e): void
    {
        // Log error
        Log::error(
            'Failed to publish post',
            [
                'platform' => $post->platform,
                'error' => $e->getMessage(),
            ],
            $post->user_id,
            $post->id
        );

        // Notify user
        $post->user->notify(new PostStatusNotification(
            $post,
            'failed',
            $e->getMessage()
        ));

        throw $e;
    }

    /**
     * Get the platform name.
     */
    protected function getPlatformName(): string
    {
        $class = get_class($this);
        return strtolower(str_replace(['App\\Services\\SocialMedia\\', 'Service'], '', $class));
    }
}
