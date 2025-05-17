<?php

namespace App\Services\SocialMedia;

use App\Models\Log;
use App\Models\Post;
use App\Models\SocialAccount;
use Exception;

abstract class BaseSocialMediaService
{
    protected SocialAccount $account;

    /**
     * Set the social account to use
     */
    public function setAccount(SocialAccount $account): self
    {
        $this->account = $account;
        return $this;
    }

    /**
     * Publish a post to the social media platform
     */
    abstract public function publish(Post $post): array;

    /**
     * Refresh the access token if needed
     */
    abstract public function refreshTokenIfNeeded(): bool;

    /**
     * Get account details from the platform
     */
    abstract public function getAccountDetails(): array;

    /**
     * Validate post content for the platform
     */
    abstract public function validateContent(string $content): bool;

    /**
     * Log success message
     */
    protected function logSuccess(string $message, array $context = [], ?int $userId = null, ?int $postId = null): void
    {
        Log::success($message, $context, $userId, $postId);
    }

    /**
     * Log error message
     */
    protected function logError(string $message, array $context = [], ?int $userId = null, ?int $postId = null): void
    {
        Log::error($message, $context, $userId, $postId);
    }

    /**
     * Log warning message
     */
    protected function logWarning(string $message, array $context = [], ?int $userId = null, ?int $postId = null): void
    {
        Log::warning($message, $context, $userId, $postId);
    }

    /**
     * Log info message
     */
    protected function logInfo(string $message, array $context = [], ?int $userId = null, ?int $postId = null): void
    {
        Log::info($message, $context, $userId, $postId);
    }

    /**
     * Format response array
     */
    protected function formatResponse(bool $success, string $message, array $data = []): array
    {
        return [
            'success' => $success,
            'message' => $message,
            'data' => $data
        ];
    }

    /**
     * Handle API exceptions
     */
    protected function handleException(Exception $e, string $operation, ?int $userId = null, ?int $postId = null): array
    {
        $this->logError(
            "Failed to {$operation}",
            [
                'error' => $e->getMessage(),
                'platform' => $this->account->platform
            ],
            $userId,
            $postId
        );

        return $this->formatResponse(false, $e->getMessage());
    }

    /**
     * Check if token needs refresh and refresh if needed
     */
    protected function ensureValidToken(): bool
    {
        if ($this->account->needsTokenRefresh()) {
            return $this->refreshTokenIfNeeded();
        }

        return true;
    }
}
