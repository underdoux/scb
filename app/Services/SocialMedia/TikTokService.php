<?php

namespace App\Services\SocialMedia;

use App\Models\Post;
use Exception;
use GuzzleHttp\Client;

class TikTokService extends BaseSocialMediaService
{
    private ?Client $client = null;
    private string $baseUrl = 'https://open.tiktokapis.com/v2';

    /**
     * Initialize TikTok client
     */
    private function initClient(): void
    {
        if (!$this->client) {
            $this->client = new Client([
                'base_uri' => $this->baseUrl,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->account->access_token,
                    'Content-Type' => 'application/json',
                ],
            ]);
        }
    }

    /**
     * Publish a post to TikTok
     */
    public function publish(Post $post): array
    {
        try {
            if (!$this->ensureValidToken()) {
                throw new Exception('Failed to refresh TikTok token');
            }

            $this->initClient();

            // Note: TikTok's API currently has limited capabilities for direct posting
            // This implementation assumes future API support for direct posting
            
            // Prepare content
            $caption = $post->content;
            if ($post->hashtags) {
                $caption .= "\n\n" . $post->hashtags;
            }

            // Create video upload
            $response = $this->client->post('/video/upload/', [
                'json' => [
                    'source' => 'VIDEO_SOURCE_URL', // This needs to be implemented based on your media handling
                    'description' => $caption,
                    'privacy_level' => 'PUBLIC',
                    'disable_duet' => false,
                    'disable_stitch' => false,
                    'disable_comment' => false,
                    'video_cover_timestamp_ms' => 0
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                $result = json_decode($response->getBody()->getContents(), true);
                
                if (isset($result['data']['video_id'])) {
                    $this->logSuccess(
                        'TikTok video published successfully',
                        ['video_id' => $result['data']['video_id']],
                        $post->user_id,
                        $post->id
                    );

                    return $this->formatResponse(true, 'Video published successfully', [
                        'video_id' => $result['data']['video_id'],
                        'video_url' => "https://www.tiktok.com/@{$this->account->platform_username}/video/{$result['data']['video_id']}"
                    ]);
                }
            }

            throw new Exception('Failed to publish video: ' . $response->getBody()->getContents());
        } catch (Exception $e) {
            return $this->handleException($e, 'publish TikTok video', $post->user_id, $post->id);
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
            
            $response = $client->post('https://open-api.tiktok.com/oauth/refresh_token/', [
                'form_params' => [
                    'client_key' => config('services.tiktok.client_id'),
                    'client_secret' => config('services.tiktok.client_secret'),
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $this->account->refresh_token,
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if (isset($result['data']['access_token'])) {
                $this->account->update([
                    'access_token' => $result['data']['access_token'],
                    'refresh_token' => $result['data']['refresh_token'] ?? $this->account->refresh_token,
                    'token_expires_at' => now()->addSeconds($result['data']['expires_in']),
                ]);

                $this->logInfo(
                    'TikTok token refreshed successfully',
                    [],
                    $this->account->user_id
                );

                return true;
            }

            throw new Exception('Failed to refresh token: ' . json_encode($result));
        } catch (Exception $e) {
            $this->logError(
                'Failed to refresh TikTok token',
                ['error' => $e->getMessage()],
                $this->account->user_id
            );

            return false;
        }
    }

    /**
     * Get account details from TikTok
     */
    public function getAccountDetails(): array
    {
        try {
            if (!$this->ensureValidToken()) {
                throw new Exception('Failed to refresh TikTok token');
            }

            $this->initClient();

            $response = $this->client->get('/user/info/', [
                'query' => [
                    'fields' => 'open_id,union_id,avatar_url,display_name,bio_description,profile_deep_link,is_verified,follower_count,following_count,likes_count,video_count'
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                $result = json_decode($response->getBody()->getContents(), true);
                $profile = $result['data']['user'];

                return $this->formatResponse(true, 'Account details retrieved successfully', [
                    'id' => $profile['open_id'],
                    'username' => $profile['display_name'],
                    'avatar_url' => $profile['avatar_url'],
                    'bio' => $profile['bio_description'],
                    'is_verified' => $profile['is_verified'],
                    'follower_count' => $profile['follower_count'],
                    'following_count' => $profile['following_count'],
                    'likes_count' => $profile['likes_count'],
                    'video_count' => $profile['video_count'],
                    'profile_url' => $profile['profile_deep_link'],
                ]);
            }

            throw new Exception('Failed to get account details');
        } catch (Exception $e) {
            return $this->handleException($e, 'get TikTok account details', $this->account->user_id);
        }
    }

    /**
     * Validate post content for TikTok
     */
    public function validateContent(string $content): bool
    {
        // TikTok's caption limit is 2,200 characters
        return mb_strlen($content) <= 2200;
    }
}
