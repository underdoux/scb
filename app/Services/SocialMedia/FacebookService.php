<?php

namespace App\Services\SocialMedia;

use App\Models\Post;
use Exception;
use Facebook\Facebook;

class FacebookService extends BaseSocialMediaService
{
    private ?Facebook $client = null;

    /**
     * Initialize Facebook client
     */
    private function initClient(): void
    {
        if (!$this->client) {
            $this->client = new Facebook([
                'app_id' => config('services.facebook.client_id'),
                'app_secret' => config('services.facebook.client_secret'),
                'default_graph_version' => 'v18.0',
            ]);
            
            if ($this->account) {
                $this->client->setDefaultAccessToken($this->account->access_token);
            }
        }
    }

    /**
     * Publish a post to Facebook
     */
    public function publish(Post $post): array
    {
        try {
            if (!$this->ensureValidToken()) {
                throw new Exception('Failed to refresh Facebook token');
            }

            $this->initClient();

            // Prepare content
            $content = $post->content;
            if ($post->hashtags) {
                $content .= "\n\n" . $post->hashtags;
            }

            // Create the post
            $response = $this->client->post('/' . $this->account->platform_user_id . '/feed', [
                'message' => $content
            ]);

            $result = $response->getGraphNode();

            if ($result && isset($result['id'])) {
                $this->logSuccess(
                    'Facebook post published successfully',
                    ['post_id' => $result['id']],
                    $post->user_id,
                    $post->id
                );

                return $this->formatResponse(true, 'Post published successfully', [
                    'post_id' => $result['id'],
                    'post_url' => "https://facebook.com/{$result['id']}"
                ]);
            }

            throw new Exception('Failed to publish post: ' . json_encode($result));
        } catch (Exception $e) {
            return $this->handleException($e, 'publish Facebook post', $post->user_id, $post->id);
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
                'grant_type' => 'fb_exchange_token',
                'client_id' => config('services.facebook.client_id'),
                'client_secret' => config('services.facebook.client_secret'),
                'fb_exchange_token' => $this->account->access_token,
            ]);

            $result = $response->getGraphNode();

            if (isset($result['access_token'])) {
                $this->account->update([
                    'access_token' => $result['access_token'],
                    'token_expires_at' => now()->addSeconds($result['expires_in'] ?? 5184000), // Default to 60 days
                ]);

                $this->logInfo(
                    'Facebook token refreshed successfully',
                    [],
                    $this->account->user_id
                );

                return true;
            }

            throw new Exception('Failed to refresh token: ' . json_encode($result));
        } catch (Exception $e) {
            $this->logError(
                'Failed to refresh Facebook token',
                ['error' => $e->getMessage()],
                $this->account->user_id
            );

            return false;
        }
    }

    /**
     * Get account details from Facebook
     */
    public function getAccountDetails(): array
    {
        try {
            if (!$this->ensureValidToken()) {
                throw new Exception('Failed to refresh Facebook token');
            }

            $this->initClient();

            $response = $this->client->get('/me?fields=id,name,accounts');
            $user = $response->getGraphUser();

            if ($user) {
                $data = [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'pages' => []
                ];

                // Get pages if available
                $accounts = $user->getField('accounts');
                if ($accounts) {
                    foreach ($accounts as $account) {
                        $data['pages'][] = [
                            'id' => $account['id'],
                            'name' => $account['name'],
                            'access_token' => $account['access_token']
                        ];
                    }
                }

                return $this->formatResponse(true, 'Account details retrieved successfully', $data);
            }

            throw new Exception('Failed to get account details');
        } catch (Exception $e) {
            return $this->handleException($e, 'get Facebook account details', $this->account->user_id);
        }
    }

    /**
     * Validate post content for Facebook
     */
    public function validateContent(string $content): bool
    {
        // Facebook's character limit is 63,206
        return mb_strlen($content) <= 63206;
    }
}
