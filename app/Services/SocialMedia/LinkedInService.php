<?php

namespace App\Services\SocialMedia;

use App\Models\Post;
use Exception;
use GuzzleHttp\Client;

class LinkedInService extends BaseSocialMediaService
{
    private ?Client $client = null;
    private string $apiVersion = 'v2';
    private string $baseUrl = 'https://api.linkedin.com';

    /**
     * Initialize LinkedIn client
     */
    private function initClient(): void
    {
        if (!$this->client) {
            $this->client = new Client([
                'base_uri' => $this->baseUrl,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->account->access_token,
                    'X-Restli-Protocol-Version' => '2.0.0',
                    'Content-Type' => 'application/json',
                ],
            ]);
        }
    }

    /**
     * Publish a post to LinkedIn
     */
    public function publish(Post $post): array
    {
        try {
            if (!$this->ensureValidToken()) {
                throw new Exception('Failed to refresh LinkedIn token');
            }

            $this->initClient();

            // Prepare content
            $content = $post->content;
            if ($post->hashtags) {
                $content .= "\n\n" . $post->hashtags;
            }

            // Create the post payload
            $payload = [
                'author' => 'urn:li:person:' . $this->account->platform_user_id,
                'lifecycleState' => 'PUBLISHED',
                'specificContent' => [
                    'com.linkedin.ugc.ShareContent' => [
                        'shareCommentary' => [
                            'text' => $content
                        ],
                        'shareMediaCategory' => 'NONE'
                    ]
                ],
                'visibility' => [
                    'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC'
                ]
            ];

            // Make the API request
            $response = $this->client->post("/rest/posts", [
                'json' => $payload
            ]);

            if ($response->getStatusCode() === 201) {
                $result = json_decode($response->getBody()->getContents(), true);
                $postId = $result['id'] ?? null;

                if ($postId) {
                    $this->logSuccess(
                        'LinkedIn post published successfully',
                        ['post_id' => $postId],
                        $post->user_id,
                        $post->id
                    );

                    return $this->formatResponse(true, 'Post published successfully', [
                        'post_id' => $postId,
                        'post_url' => "https://www.linkedin.com/feed/update/{$postId}"
                    ]);
                }
            }

            throw new Exception('Failed to publish post: ' . $response->getBody()->getContents());
        } catch (Exception $e) {
            return $this->handleException($e, 'publish LinkedIn post', $post->user_id, $post->id);
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

            $client = new Client();
            
            $response = $client->post('https://www.linkedin.com/oauth/v2/accessToken', [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $this->account->refresh_token,
                    'client_id' => config('services.linkedin.client_id'),
                    'client_secret' => config('services.linkedin.client_secret'),
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if (isset($result['access_token'])) {
                $this->account->update([
                    'access_token' => $result['access_token'],
                    'refresh_token' => $result['refresh_token'] ?? $this->account->refresh_token,
                    'token_expires_at' => now()->addSeconds($result['expires_in']),
                ]);

                $this->logInfo(
                    'LinkedIn token refreshed successfully',
                    [],
                    $this->account->user_id
                );

                return true;
            }

            throw new Exception('Failed to refresh token: ' . json_encode($result));
        } catch (Exception $e) {
            $this->logError(
                'Failed to refresh LinkedIn token',
                ['error' => $e->getMessage()],
                $this->account->user_id
            );

            return false;
        }
    }

    /**
     * Get account details from LinkedIn
     */
    public function getAccountDetails(): array
    {
        try {
            if (!$this->ensureValidToken()) {
                throw new Exception('Failed to refresh LinkedIn token');
            }

            $this->initClient();

            $response = $this->client->get("/v2/me", [
                'query' => [
                    'projection' => '(id,localizedFirstName,localizedLastName,profilePicture(displayImage~:playableStreams))'
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                $profile = json_decode($response->getBody()->getContents(), true);

                return $this->formatResponse(true, 'Account details retrieved successfully', [
                    'id' => $profile['id'],
                    'first_name' => $profile['localizedFirstName'],
                    'last_name' => $profile['localizedLastName'],
                    'full_name' => $profile['localizedFirstName'] . ' ' . $profile['localizedLastName'],
                    'profile_picture' => $profile['profilePicture']['displayImage~']['elements'][0]['identifiers'][0]['identifier'] ?? null,
                ]);
            }

            throw new Exception('Failed to get account details');
        } catch (Exception $e) {
            return $this->handleException($e, 'get LinkedIn account details', $this->account->user_id);
        }
    }

    /**
     * Validate post content for LinkedIn
     */
    public function validateContent(string $content): bool
    {
        // LinkedIn's character limit for posts is 3,000
        return mb_strlen($content) <= 3000;
    }
}
