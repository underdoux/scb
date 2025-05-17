<?php

namespace App\Services\SocialMedia;

use App\Models\Post;
use Exception;
use Facebook\Facebook;

class InstagramService extends BaseSocialMediaService
{
    private ?Facebook $client = null;

    /**
     * Initialize Instagram client (using Facebook Graph API)
     */
    private function initClient(): void
    {
        if (!$this->client) {
            $this->client = new Facebook([
                'app_id' => config('services.instagram.client_id'),
                'app_secret' => config('services.instagram.client_secret'),
                'default_graph_version' => 'v18.0',
            ]);
            
            if ($this->account) {
                $this->client->setDefaultAccessToken($this->account->access_token);
            }
        }
    }

    /**
     * Publish a post to Instagram
     */
    public function publish(Post $post): array
    {
        try {
            if (!$this->ensureValidToken()) {
                throw new Exception('Failed to refresh Instagram token');
            }

            $this->initClient();

            // Prepare content
            $caption = $post->content;
            if ($post->hashtags) {
                $caption .= "\n\n" . $post->hashtags;
            }

            // First, create a container
            $response = $this->client->post('/' . $this->account->platform_user_id . '/media', [
                'caption' => $caption,
                'image_url' => 'URL_TO_IMAGE', // This needs to be implemented based on your media handling
                'access_token' => $this->account->access_token,
            ]);

            $container = $response->getGraphNode();

            if (!isset($container['id'])) {
                throw new Exception('Failed to create media container');
            }

            // Then publish the container
            $response = $this->client->post('/' . $this->account->platform_user_id . '/media_publish', [
                'creation_id' => $container['id'],
                'access_token' => $this->account->access_token,
            ]);

            $result = $response->getGraphNode();

            if ($result && isset($result['id'])) {
                $this->logSuccess(
                    'Instagram post published successfully',
                    ['post_id' => $result['id']],
                    $post->user_id,
                    $post->id
                );

                return $this->formatResponse(true, 'Post published successfully', [
                    'post_id' => $result['id'],
                    'post_url' => "https://instagram.com/p/{$result['id']}"
                ]);
            }

            throw new Exception('Failed to publish post: ' . json_encode($result));
        } catch (Exception $e) {
            return $this->handleException($e, 'publish Instagram post', $post->user_id, $post->id);
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
            
            // Exchange the short-lived token for a long-lived one
            $response = $this->client->get('/oauth/access_token', [
                'grant_type' => 'ig_exchange_token',
                'client_secret' => config('services.instagram.client_secret'),
                'access_token' => $this->account->access_token,
            ]);

            $result = $response->getGraphNode();

            if (isset($result['access_token'])) {
                $this->account->update([
                    'access_token' => $result['access_token'],
                    'token_expires_at' => now()->addSeconds($result['expires_in'] ?? 5184000), // Default to 60 days
                ]);

                $this->logInfo(
                    'Instagram token refreshed successfully',
                    [],
                    $this->account->user_id
                );

                return true;
            }

            throw new Exception('Failed to refresh token: ' . json_encode($result));
        } catch (Exception $e) {
            $this->logError(
                'Failed to refresh Instagram token',
                ['error' => $e->getMessage()],
                $this->account->user_id
            );

            return false;
        }
    }

    /**
     * Get account details from Instagram
     */
    public function getAccountDetails(): array
    {
        try {
            if (!$this->ensureValidToken()) {
                throw new Exception('Failed to refresh Instagram token');
            }

            $this->initClient();

            $response = $this->client->get('/' . $this->account->platform_user_id, [
                'fields' => 'id,username,media_count,followers_count,follows_count',
                'access_token' => $this->account->access_token,
            ]);

            $profile = $response->getGraphNode();

            if ($profile) {
                return $this->formatResponse(true, 'Account details retrieved successfully', [
                    'id' => $profile['id'],
                    'username' => $profile['username'],
                    'media_count' => $profile['media_count'],
                    'followers_count' => $profile['followers_count'],
                    'follows_count' => $profile['follows_count'],
                ]);
            }

            throw new Exception('Failed to get account details');
        } catch (Exception $e) {
            return $this->handleException($e, 'get Instagram account details', $this->account->user_id);
        }
    }

    /**
     * Validate post content for Instagram
     */
    public function validateContent(string $content): bool
    {
        // Instagram's caption limit is 2,200 characters
        return mb_strlen($content) <= 2200;
    }
}
