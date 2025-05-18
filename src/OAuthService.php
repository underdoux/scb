<?php
class OAuthService {
    private $pdo;
    private $config;

    public function __construct() {
        require_once __DIR__ . '/../db/dbConnection.php';
        $this->pdo = getDBConnection();
        $this->config = require_once __DIR__ . '/../config/oauth_config.php';
    }

    public function getAuthUrl(string $platform): string {
        if (!isset($this->config[$platform])) {
            throw new Exception("Unsupported platform: $platform");
        }

        $config = $this->config[$platform];
        $params = [
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect_uri'],
            'scope' => implode(' ', $config['scopes']),
            'response_type' => 'code',
            'state' => bin2hex(random_bytes(16))
        ];

        // Platform specific parameters
        if ($platform === 'twitter') {
            $params['code_challenge_method'] = 'S256';
            $params['code_challenge'] = $this->generateCodeChallenge();
        }

        // Store state in session for verification
        $_SESSION['oauth_state'] = $params['state'];
        if (isset($params['code_challenge'])) {
            $_SESSION['code_verifier'] = $_SESSION['code_verifier'] ?? $this->generateCodeVerifier();
        }

        return $config['auth_url'] . '?' . http_build_query($params);
    }

    public function handleCallback(string $platform, string $code, string $state): array {
        if (!isset($_SESSION['oauth_state']) || $state !== $_SESSION['oauth_state']) {
            throw new Exception('Invalid state parameter');
        }

        $config = $this->config[$platform];
        $params = [
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'code' => $code,
            'redirect_uri' => $config['redirect_uri'],
            'grant_type' => 'authorization_code'
        ];

        // Platform specific parameters
        if ($platform === 'twitter') {
            $params['code_verifier'] = $_SESSION['code_verifier'];
            unset($params['client_secret']); // Twitter doesn't use client_secret in token request
        }

        $ch = curl_init($config['token_url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        
        if ($platform === 'twitter') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: Basic ' . base64_encode($config['client_id'] . ':' . $config['client_secret'])
            ]);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception("Token request failed with status $httpCode: $response");
        }

        return json_decode($response, true);
    }

    public function saveToken(int $userId, string $platform, array $tokenData): void {
        $sql = "INSERT OR REPLACE INTO oauth_tokens 
                (user_id, platform, access_token, refresh_token, expires_at) 
                VALUES (:user_id, :platform, :access_token, :refresh_token, :expires_at)";

        $expiresAt = new DateTime();
        $expiresAt->modify('+' . ($tokenData['expires_in'] ?? 3600) . ' seconds');

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':platform' => $platform,
                ':access_token' => $tokenData['access_token'],
                ':refresh_token' => $tokenData['refresh_token'] ?? null,
                ':expires_at' => $expiresAt->format('Y-m-d H:i:s')
            ]);
        } catch (PDOException $e) {
            throw new Exception("Failed to save token: " . $e->getMessage());
        }
    }

    public function getToken(int $userId, string $platform): ?array {
        $sql = "SELECT * FROM oauth_tokens WHERE user_id = :user_id AND platform = :platform";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':user_id' => $userId, ':platform' => $platform]);
            $token = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($token && $this->isTokenExpired($token)) {
                return $this->refreshToken($userId, $platform);
            }

            return $token;
        } catch (PDOException $e) {
            throw new Exception("Failed to get token: " . $e->getMessage());
        }
    }

    private function isTokenExpired(array $token): bool {
        if (!isset($token['expires_at'])) {
            return true;
        }

        $expiresAt = new DateTime($token['expires_at']);
        $now = new DateTime();
        return $now > $expiresAt;
    }

    private function generateCodeVerifier(): string {
        $random = bin2hex(random_bytes(32));
        return rtrim(strtr(base64_encode($random), '+/', '-_'), '=');
    }

    private function generateCodeChallenge(): string {
        $verifier = $_SESSION['code_verifier'] ?? $this->generateCodeVerifier();
        $challenge = hash('sha256', $verifier, true);
        return rtrim(strtr(base64_encode($challenge), '+/', '-_'), '=');
    }

    public function refreshToken(int $userId, string $platform): ?array {
        $token = $this->getToken($userId, $platform);
        if (!$token || !$token['refresh_token']) {
            return null;
        }

        $config = $this->config[$platform];
        $params = [
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'refresh_token' => $token['refresh_token'],
            'grant_type' => 'refresh_token'
        ];

        $ch = curl_init($config['token_url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception("Token refresh failed with status $httpCode: $response");
        }

        $tokenData = json_decode($response, true);
        $this->saveToken($userId, $platform, $tokenData);

        return $tokenData;
    }

    public function disconnectPlatform(int $userId, string $platform): void {
        $sql = "DELETE FROM oauth_tokens WHERE user_id = :user_id AND platform = :platform";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':user_id' => $userId, ':platform' => $platform]);
        } catch (PDOException $e) {
            throw new Exception("Failed to disconnect platform: " . $e->getMessage());
        }
    }
}
?>
