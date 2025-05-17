<?php

namespace App\Services;

use OpenAI\Client;
use App\Models\Log;
use Exception;

class OpenAIService
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Generate social media content based on a prompt
     */
    public function generateContent(string $prompt, ?int $userId = null): array
    {
        try {
            $response = $this->client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a professional social media content creator. Create engaging, platform-appropriate content.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 200,
            ]);

            $content = $response->choices[0]->message->content;

            // Log success
            Log::success(
                'Content generated successfully',
                ['prompt' => $prompt],
                $userId
            );

            // Parse content and hashtags
            $parts = $this->parseContent($content);

            return [
                'success' => true,
                'content' => $parts['content'],
                'hashtags' => $parts['hashtags'],
                'raw_response' => $content
            ];
        } catch (Exception $e) {
            // Log error
            Log::error(
                'Failed to generate content',
                [
                    'prompt' => $prompt,
                    'error' => $e->getMessage()
                ],
                $userId
            );

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Parse the generated content to separate main content and hashtags
     */
    private function parseContent(string $content): array
    {
        // Split content by hashtag indicator
        $parts = explode('#', $content, 2);

        if (count($parts) === 1) {
            return [
                'content' => trim($parts[0]),
                'hashtags' => ''
            ];
        }

        return [
            'content' => trim($parts[0]),
            'hashtags' => '#' . trim($parts[1])
        ];
    }

    /**
     * Generate variations of the content
     */
    public function generateVariations(string $content, int $count = 3, ?int $userId = null): array
    {
        try {
            $response = $this->client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are a professional social media content creator. Create {$count} variations of the given content while maintaining the same message and tone."
                    ],
                    [
                        'role' => 'user',
                        'content' => $content
                    ]
                ],
                'temperature' => 0.8,
                'max_tokens' => 300,
            ]);

            $variations = $response->choices[0]->message->content;

            // Log success
            Log::success(
                'Content variations generated successfully',
                ['original_content' => $content],
                $userId
            );

            return [
                'success' => true,
                'variations' => $this->parseVariations($variations),
                'raw_response' => $variations
            ];
        } catch (Exception $e) {
            // Log error
            Log::error(
                'Failed to generate content variations',
                [
                    'original_content' => $content,
                    'error' => $e->getMessage()
                ],
                $userId
            );

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Parse the generated variations into an array
     */
    private function parseVariations(string $variations): array
    {
        // Split by numbered lines (1., 2., 3., etc.)
        $lines = preg_split('/\d+\.\s+/', $variations, -1, PREG_SPLIT_NO_EMPTY);
        return array_map('trim', $lines);
    }
}
