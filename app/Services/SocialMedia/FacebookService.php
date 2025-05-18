<?php

namespace App\Services\SocialMedia;

use App\Models\Post;
use Facebook\Facebook;
use Facebook\Exceptions\FacebookSDKException;
use Exception;

class FacebookService extends BaseSocialMediaService
{
    protected Facebook $fb;

    public function __construct()
    {
        $this->fb = new Facebook([
            'app_id' => config('services.facebook.client_id'),
            'app_secret' => config('services.facebook.client_secret'),
            'default_graph_version' => config('services.facebook.default_graph_version', 'v18.0'),
        ]);
    }

    /**
     * Get the account details from Facebook.
     */
    public function getAccountDetails(): array
    {
        try {
            if (!$this->account) {
                throw new Exception('No Facebook account set');
            }

            $this->fb->setDefaultAccessToken($this->account->access_token);

            $response = $this->fb->get('/me?fields=id,name,picture');
            $user = $response->getGraphUser();

            return [
                'success' => true,
                'data' => [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'picture' => $user->getPicture()->getUrl(),
                ],
            ];
        } catch (FacebookSDKException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Publish a post to Facebook.
     */
    public function publish(Post $post): array
    {
        try {
            if (!$this->account) {
                throw new Exception('No Facebook account set');
            }

            // Validate content
            $this->validateContent($post);

            // Format content for Facebook
            $data = $this->formatContent($post);

            // Set access token
            $this->fb->setDefaultAccessToken($this->account->access_token);

            // Publish to Facebook
            $response = $this->fb->post('/me/feed', $data);
            $graphNode = $response->getGraphNode();

            $postId = $graphNode->getField('id');
            $postUrl = "https://facebook.com/{$postId}";

            // Handle success
            $this->handleSuccess($post, [
                'post_id' => $postId,
                'post_url' => $postUrl,
            ]);

            return [
                'success' => true,
                'data' => [
                    'post_id' => $postId,
                    'post_url' => $postUrl,
                ],
            ];

        } catch (Exception $e) {
            $this->handleError($post, $e);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Refresh the Facebook access token.
     */
    protected function refreshToken(): array
    {
        try {
            if (!$this->account || !$this->account->refresh_token) {
                throw new Exception('No refresh token available');
            }

            $oauth2Client = $this->fb->getOAuth2Client();
            
            // Exchange refresh token for a new access token
            $accessToken = $oauth2Client->getLongLivedAccessToken($this->account->refresh_token);

            return [
                'success' => true,
                'data' => [
                    'access_token' => $accessToken->getValue(),
                    'expires_in' => $accessToken->getExpiresAt()->getTimestamp() - time(),
                ],
            ];

        } catch (FacebookSDKException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Validate post content for Facebook.
     */
    protected function validateContent(Post $post): void
    {
        $content = $post->content;
        $maxLength = 63206; // Facebook's maximum character limit

        if (empty($content)) {
            throw new Exception('Post content cannot be empty');
        }

        if (mb_strlen($content) > $maxLength) {
            throw new Exception("Content exceeds Facebook's maximum length of {$maxLength} characters");
        }
    }

    /**
     * Format post content for Facebook.
     */
    protected function formatContent(Post $post): array
    {
        $data = ['message' => $post->content];

        // Add link if present
        if (!empty($post->link)) {
            $data['link'] = $post->link;
        }

        // Add media if present
        if (!empty($post->media)) {
            foreach ($post->media as $media) {
                switch ($media['type']) {
                    case 'image':
                        // For images, we need to upload them first
                        $photoResponse = $this->fb->post('/me/photos', [
                            'source' => $this->fb->fileToUpload($media['path']),
                            'published' => false,
                        ]);
                        $photo = $photoResponse->getGraphNode();
                        $data['attached_media'][] = ['media_fbid' => $photo->getField('id')];
                        break;

                    case 'video':
                        // For videos, we need to use a different endpoint
                        $videoResponse = $this->fb->post('/me/videos', [
                            'source' => $this->fb->videoToUpload($media['path']),
                            'title' => $media['title'] ?? '',
                            'description' => $post->content,
                        ]);
                        // Since video upload creates its own post, we don't need to proceed with the regular post
                        return [];
                }
            }
        }

        return $data;
    }
}
