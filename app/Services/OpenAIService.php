<?php

namespace App\Services;

use App\Models\Log;
use Illuminate\Support\Facades\Http;
use Exception;

class OpenAIService
{
    protected string $apiKey;
    protected string $model;
    protected float $temperature;
    protected int $maxTokens;
    protected string $baseUrl = 'https://api.openai.com/v1';

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->model = config('services.openai.model', 'gpt-4');
        $this->temperature = (float) config('services.openai.temperature', 0.7);
        $this->maxTokens = (int) config('services.openai.max_tokens', 200);
    }

    /**
     * Generate social media post content
     */
    public function generatePost(string $topic, string $platform, array $options = []): array
    {
        try {
            $prompt = $this->buildPrompt($topic, $platform, $options);
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/chat/completions", [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $this->getSystemPrompt($platform),
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => $this->temperature,
                'max_tokens' => $this->maxTokens,
            ]);

            if (!$response->successful()) {
                throw new Exception('OpenAI API request failed: ' . $response->body());
            }

            $result = $response->json();
            $content = $result['choices'][0]['message']['content'] ?? null;

            if (!$content) {
                throw new Exception('No content generated');
            }

            // Parse the response into structured data
            return $this->parseResponse($content, $platform);

        } catch (Exception $e) {
            Log::error(
                'Failed to generate content with OpenAI',
                [
                    'topic' => $topic,
                    'platform' => $platform,
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;
        }
    }

    /**
     * Build the prompt for content generation
     */
    protected function buildPrompt(string $topic, string $platform, array $options): string
    {
        $toneOptions = [
            'professional' => 'Use a professional and formal tone',
            'casual' => 'Use a casual and friendly tone',
            'humorous' => 'Include humor and keep it light-hearted',
            'informative' => 'Focus on providing valuable information',
            'promotional' => 'Create engaging promotional content',
        ];

        $tone = $options['tone'] ?? 'professional';
        $includeHashtags = $options['include_hashtags'] ?? true;
        $includeEmoji = $options['include_emoji'] ?? true;
        $callToAction = $options['call_to_action'] ?? true;

        $prompt = "Create a {$platform} post about: {$topic}\n\n";
        $prompt .= "Requirements:\n";
        $prompt .= "- {$toneOptions[$tone]}\n";
        $prompt .= "- Stay within {$platform}'s character limits\n";
        
        if ($includeHashtags) {
            $prompt .= "- Include relevant hashtags\n";
        }
        
        if ($includeEmoji) {
            $prompt .= "- Include appropriate emojis\n";
        }
        
        if ($callToAction) {
            $prompt .= "- Include a compelling call-to-action\n";
        }

        $prompt .= "\nFormat the response as JSON with these fields:\n";
        $prompt .= "- content: The main post content\n";
        $prompt .= "- hashtags: Array of hashtags (if included)\n";
        $prompt .= "- cta: The call-to-action used (if included)\n";

        return $prompt;
    }

    /**
     * Get platform-specific system prompt
     */
    protected function getSystemPrompt(string $platform): string
    {
        $basePrompt = "You are a social media content expert specializing in {$platform}. ";
        
        return $basePrompt . match ($platform) {
            'facebook' => "Create engaging content that encourages discussion and sharing. Max length: 63,206 characters.",
            'twitter' => "Create concise, impactful content. Max length: 280 characters.",
            'instagram' => "Create visually descriptive content with emotional appeal. Max length: 2,200 characters.",
            'linkedin' => "Create professional content that demonstrates expertise. Max length: 3,000 characters.",
            'tiktok' => "Create trendy, attention-grabbing content. Max length: 2,200 characters.",
            'youtube' => "Create descriptive content optimized for video. Max length: 5,000 characters.",
            default => "Create platform-appropriate social media content.",
        };
    }

    /**
     * Parse the AI response into structured data
     */
    protected function parseResponse(string $content, string $platform): array
    {
        try {
            // Try to parse as JSON first
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            
            // Validate required fields
            if (!isset($data['content'])) {
                throw new Exception('Response missing required content field');
            }

            return [
                'content' => $data['content'],
                'hashtags' => $data['hashtags'] ?? [],
                'cta' => $data['cta'] ?? null,
                'platform' => $platform,
                'raw_response' => $content,
            ];

        } catch (Exception $e) {
            // If JSON parsing fails, try to extract content directly
            return [
                'content' => $content,
                'hashtags' => [],
                'cta' => null,
                'platform' => $platform,
                'raw_response' => $content,
            ];
        }
    }

    /**
     * Generate variations of a post
     */
    public function generateVariations(string $content, string $platform, int $count = 3): array
    {
        $prompt = "Generate {$count} variations of this {$platform} post:\n\n{$content}\n\n";
        $prompt .= "Keep the same message but vary the wording and style. Format as JSON array of variations.";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/chat/completions", [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are a social media content expert. Generate creative variations while maintaining the core message.",
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.8, // Slightly higher for more variation
                'max_tokens' => $this->maxTokens * $count,
            ]);

            if (!$response->successful()) {
                throw new Exception('OpenAI API request failed: ' . $response->body());
            }

            $result = $response->json();
            $content = $result['choices'][0]['message']['content'] ?? null;

            if (!$content) {
                throw new Exception('No variations generated');
            }

            // Parse variations
            $variations = json_decode($content, true);
            if (!is_array($variations)) {
                $variations = [$content];
            }

            return array_map(fn($variation) => is_array($variation) ? $variation['content'] : $variation, $variations);

        } catch (Exception $e) {
            Log::error(
                'Failed to generate content variations',
                [
                    'original_content' => $content,
                    'platform' => $platform,
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;
        }
    }
}
