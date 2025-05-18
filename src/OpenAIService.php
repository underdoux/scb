<?php
class OpenAIService {
    private $config;
    private $apiKey;
    private $cacheService;

    public function __construct() {
        $this->config = require_once __DIR__ . '/../config/openai_config.php';
        $this->apiKey = $this->config['api_key'];
        $this->cacheService = new CacheService();
        
        if (empty($this->apiKey)) {
            throw new Exception('OpenAI API key is not configured');
        }
    }

    private function getCacheKey(array $params): array {
        return [
            'topic' => $params['topic'],
            'contentType' => $params['contentType'],
            'length' => $params['length'],
            'tone' => $params['tone']
        ];
    }

    public function generateSocialContent(string $topic, string $contentType = 'engaging', string $length = 'medium', string $tone = 'professional', int $userId = null): array {
        // Check rate limit if userId is provided
        if ($userId !== null) {
            if (!$this->cacheService->checkRateLimit($userId)) {
                throw new Exception('Rate limit exceeded. Please try again in a minute.');
            }
        }

        // Try to get cached content
        $cacheKey = $this->getCacheKey([
            'topic' => $topic,
            'contentType' => $contentType,
            'length' => $length,
            'tone' => $tone
        ]);

        $cachedContent = $this->cacheService->getCachedContent($cacheKey);
        if ($cachedContent !== null) {
            return $cachedContent;
        }
        // Define character limits based on length parameter
        $charLimits = [
            'short' => 100,
            'medium' => 200,
            'long' => 280
        ];
        
        $charLimit = $charLimits[$length] ?? 200;
        
        // Create system message based on content type and tone
        $systemPrompt = "You are a social media expert specializing in {$contentType} content with a {$tone} tone. ";
        $systemPrompt .= "Generate engaging content that resonates with the audience while maintaining brand voice and message clarity.";
        
        // Create user prompt with specific instructions
        $userPrompt = "Create {$contentType} social media content about: {$topic}\n";
        $userPrompt .= "Use a {$tone} tone and keep the caption within {$charLimit} characters.\n\n";
        $userPrompt .= "Include:\n";
        $userPrompt .= "1. A compelling caption (max {$charLimit} characters)\n";
        $userPrompt .= "2. 5-7 relevant hashtags\n";
        $userPrompt .= "3. 2 alternative caption variations (also max {$charLimit} characters each)\n";
        $userPrompt .= "Format as JSON with keys: caption, hashtags (array), variations (array)";
        
        $messages = [
            [
                'role' => 'system',
                'content' => $systemPrompt
            ],
            [
                'role' => 'user',
                'content' => $userPrompt
            ]
        ];

        // Generate new content if not in cache
        $response = $this->makeRequest([
            'model' => $this->config['model'],
            'messages' => $messages,
            'max_tokens' => $this->config['max_tokens'],
            'temperature' => $this->config['temperature'],
            'response_format' => ['type' => 'json_object']
        ]);

        $content = json_decode($response, true);
        
        // Cache the generated content
        $this->cacheService->cacheContent($cacheKey, $content);
        
        return $content;
    }

    private function makeRequest(array $data): string {
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        
        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
        ];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new Exception('OpenAI API request failed: ' . curl_error($ch));
        }
        
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception('OpenAI API request failed with status ' . $httpCode . ': ' . $response);
        }

        $responseData = json_decode($response, true);
        return $responseData['choices'][0]['message']['content'];
    }
}
?>
