<?php

namespace App\Services\SocialMedia;

use App\Models\Post;
use Exception;
use Google_Client;
use Google_Service_YouTube;

class YouTubeService extends BaseSocialMediaService
{
    private ?Google_Client $client = null;
    private ?Google_Service_YouTube $youtube = null;

    /**
     * Initialize YouTube client
     */
    private function initClient(): void
    {
        if (!$this->client) {
            $this->client = new Google_Client();
            $this->client->setClientId(config('services.youtube.client_id'));
            $this->client->setClientSecret(config('services.youtube.client_secret'));
            $this->client->setRedirectUri(config('services.youtube.redirect_uri'));
            $this->client->setScopes([
                'https://www.googleapis.com/auth/youtube.upload',
                'https://www.googleapis.com/auth/youtube.readonly'
            ]);
            
            if ($this->account) {
                $this->client->setAccessToken([
                    'access_token' => $this->account->access_token,
                    'refresh_token' => $this->account->refresh_token,
                    'expires_in' => $this->account->token_expires_at->diffInSeconds(now()),
                ]);
            }

            $this->youtube = new Google_Service_YouTube($this->client);
        }
    }

    /**
     * Publish a post to YouTube (as Shorts)
     */
    public function publish(Post $post): array
    {
        try {
            if (!$this->ensureValidToken()) {
                throw new Exception('Failed to refresh YouTube token');
            }

            $this->initClient();

            // Prepare content
            $title = substr($post->content, 0, 100); // YouTube title limit
            $description = $post->content;
            if ($post->hashtags) {
                $description .= "\n\n" . $post->hashtags;
            }

            // Create a snippet with video details
            $snippet = new \Google_Service_YouTube_VideoSnippet();
            $snippet->setTitle($title);
            $snippet->setDescription($description);
            $snippet->setCategoryId("22"); // People & Blogs category
            $snippet->setTags(explode(' ', str_replace('#', '', $post->hashtags)));

            // Create status with privacy setting
            $status = new \Google_Service_YouTube_VideoStatus();
            $status->setPrivacyStatus('public');

            // Create the video resource
            $video = new \Google_Service_YouTube_Video();
            $video->setSnippet($snippet);
            $video->setStatus($status);

            // Execute the upload
            // Note: Actual file upload implementation needed
            $response = $this->youtube->videos->insert(
                'snippet,status',
                $video,
                [
                    'data' => 'VIDEO_FILE_DATA', // This needs to be implemented based on your media handling
                    'mimeType' => 'video/mp4',
                    'uploadType' => 'multipart'
                ]
            );

            if ($response->getId()) {
                $this->logSuccess(
                    'YouTube video published successfully',
                    ['video_id' => $response->getId()],
                    $post->user_id,
                    $post->id
                );

                return $this->formatResponse(true, 'Video published successfully', [
                    'video_id' => $response->getId(),
                    'video_url' => "https://www.youtube.com/watch?v={$response->getId()}"
                ]);
            }

            throw new Exception('Failed to publish video');
        } catch (Exception $e) {
            return $this->handleException($e, 'publish YouTube video', $post->user_id, $post->id);
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
            
            if ($this->client->isAccessTokenExpired()) {
                $newToken = $this->client->fetchAccessTokenWithRefreshToken($this->account->refresh_token);
                
                if (isset($newToken['access_token'])) {
                    $this->account->update([
                        'access_token' => $newToken['access_token'],
                        'refresh_token' => $newToken['refresh_token'] ?? $this->account->refresh_token,
                        'token_expires_at' => now()->addSeconds($newToken['expires_in']),
                    ]);

                    $this->logInfo(
                        'YouTube token refreshed successfully',
                        [],
                        $this->account->user_id
                    );

                    return true;
                }
            }

            return true;
        } catch (Exception $e) {
            $this->logError(
                'Failed to refresh YouTube token',
                ['error' => $e->getMessage()],
                $this->account->user_id
            );

            return false;
        }
    }

    /**
     * Get account details from YouTube
     */
    public function getAccountDetails(): array
    {
        try {
            if (!$this->ensureValidToken()) {
                throw new Exception('Failed to refresh YouTube token');
            }

            $this->initClient();

            $response = $this->youtube->channels->listChannels('snippet,statistics', [
                'mine' => true
            ]);

            if ($response->getItems()) {
                $channel = $response->getItems()[0];
                $snippet = $channel->getSnippet();
                $statistics = $channel->getStatistics();

                return $this->formatResponse(true, 'Account details retrieved successfully', [
                    'id' => $channel->getId(),
                    'title' => $snippet->getTitle(),
                    'description' => $snippet->getDescription(),
                    'thumbnail_url' => $snippet->getThumbnails()->getDefault()->getUrl(),
                    'subscriber_count' => $statistics->getSubscriberCount(),
                    'video_count' => $statistics->getVideoCount(),
                    'view_count' => $statistics->getViewCount(),
                    'channel_url' => "https://www.youtube.com/channel/{$channel->getId()}"
                ]);
            }

            throw new Exception('Failed to get account details');
        } catch (Exception $e) {
            return $this->handleException($e, 'get YouTube account details', $this->account->user_id);
        }
    }

    /**
     * Validate post content for YouTube
     */
    public function validateContent(string $content): bool
    {
        // YouTube's title limit is 100 characters
        // Description limit is 5,000 characters
        return mb_strlen($content) <= 5000;
    }
}
