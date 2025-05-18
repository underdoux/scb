<?php
class OpenAIService {
    private $config;
    private $apiKey;

    public function __construct() {
        $this->config = require_once __DIR__ . '/../config/openai_config.php';
        $this->apiKey = $this->config['api_key'];
        
        if (empty($this->apiKey)) {
            throw new Exception('OpenAI API key is not configured');
        }
    }

    public function generateSocialContent(string $topic): array {
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a social media expert. Generate engaging content with a caption, relevant hashtags, and variations. Format the response as JSON.'
            ],
            [
                'role' => 'user',
                'content' => "Create social media content for the topic: {$topic}. Include:
                1. A compelling caption (max 280 characters)
                2. 5-7 relevant hashtags
                3. 2 alternative caption variations
                Format as JSON with keys: caption, hashtags (array), variations (array)"
            ]
        ];

        $response = $this->makeRequest([
            'model' => $this->config['model'],
            'messages' => $messages,
            'max_tokens' => $this->config['max_tokens'],
            'temperature' => $this->config['temperature'],
            'response_format' => ['type' => 'json_object']
        ]);

        return json_decode($response, true);
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
