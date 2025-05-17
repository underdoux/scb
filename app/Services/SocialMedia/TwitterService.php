<?php

namespace App\Services\SocialMedia;

use App\Models\Post;
use Exception;
use Abraham\TwitterOAuth\TwitterOAuth;

class TwitterService extends BaseSocialMediaService
{
    private ?TwitterOAuth $client = null;

    /**
     * Initialize Twitter client
     */
    private function initClient(): void
    {
        if (!$this->client) {
            $this->client = new TwitterOAuth(
                config('services.twitter.client_id'),
                config('services.twitter.client_secret'),
                $this->account->access_token,
                $this->account->refresh_token
            );
        }
    }

    /**
     * Publish a post to Twitter
     */
    public function publish(Post $post): array
    {
        try {
            if (!$this->ensureValidToken()) {
                throw new Exception('Failed to refresh Twitter token');
            }

            $this->initClient();

            // Prepare content
            $content = $post->content;
            if ($post->hashtags) {
                $content .= "\n\n" . $post->hashtags;
            }

            // Post tweet
            $tweet = $this->client->post('tweets', ['text' => $content], true);

            if ($this->client->getLastHttpCode() === 201) {
                $this->logSuccess(
                    'Tweet published successfully',
                    ['tweet_id' => $tweet->data->id],
                    $post->user_id,
                    $post->id
                );

                return $this->formatResponse(true, 'Tweet published successfully', [
                    'tweet_id' => $tweet->data->id,
                    'tweet_url' => "https://twitter.com/user/status/{$tweet->data->id}"
                ]);
            }

            throw new Exception('Failed to publish tweet: ' . json_encode($tweet));
        } catch (Exception $e) {
            return $this->handleException($e, 'publish tweet', $post->user_id, $post->id);
        }
    }

    /**
     * Refresh the access token if needed
     */
    public function refreshTokenIfNeeded(): bool
    {
        try {
            if (!$this->account->needsTokenRefresh()) {
                return true;
            }

            $this->initClient();
            
            // Refresh token using OAuth 2.0 refresh flow
            $result = $this->client->oauth2('oauth2/token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $this->account->refresh_token,
            ]);

            if ($this->client->getLastHttpCode() === 200) {
                $this->account->update([
                    'access_token' => $result->access_token,
                    'refresh_token' => $result->refresh_token ?? $this->account->refresh_token,
                    'token_expires_at' => now()->addSeconds($result->expires_in),
                ]);

                $this->logInfo(
                    'Twitter token refreshed successfully',
                    [],
                    $this->account->user_id
                );

                return true;
            }

            throw new Exception('Failed to refresh token: ' . json_encode($result));
        } catch (Exception $e) {
            $this->logError(
                'Failed to refresh Twitter token',
                ['error' => $e->getMessage()],
                $this->account->user_id
            );

            return false;
        }
    }

    /**
     * Get account details from Twitter
     */
    public function getAccountDetails(): array
    {
        try {
            if (!$this->ensureValidToken()) {
                throw new Exception('Failed to refresh Twitter token');
            }

            $this->initClient();

            $user = $this->client->get('users/me', ['user.fields' => 'public_metrics']);

            if ($this->client->getLastHttpCode() === 200) {
                return $this->formatResponse(true, 'Account details retrieved successfully', [
                    'username' => $user->data->username,
                    'name' => $user->data->name,
                    'followers_count' => $user->data->public_metrics->followers_count,
                    'following_count' => $user->data->public_metrics->following_count,
                    'tweet_count' => $user->data->public_metrics->tweet_count,
                ]);
            }

            throw new Exception('Failed to get account details: ' . json_encode($user));
        } catch (Exception $e) {
            return $this->handleException($e, 'get account details', $this->account->user_id);
        }
    }

    /**
     * Validate post content for Twitter
     */
    public function validateContent(string $content): bool
    {
        // Twitter's character limit is 280
        return mb_strlen($content) <= 280;
    }
}
