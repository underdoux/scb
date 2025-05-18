<?php
class ConfigService {
    private $pdo;
    private $configFiles = [
        'oauth' => __DIR__ . '/../config/oauth_config.php',
        'openai' => __DIR__ . '/../config/openai_config.php'
    ];

    public function __construct() {
        require_once __DIR__ . '/../db/dbConnection.php';
        $this->pdo = getDBConnection();
        $this->initializeTable();
    }

    private function initializeTable(): void {
        $sql = "CREATE TABLE IF NOT EXISTS api_configs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            platform VARCHAR(50) NOT NULL,
            key_name VARCHAR(100) NOT NULL,
            key_value TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(platform, key_name)
        )";

        try {
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            throw new Exception("Failed to create api_configs table: " . $e->getMessage());
        }
    }

    public function saveConfig(string $platform, array $config): void {
        foreach ($config as $key => $value) {
            $sql = "INSERT OR REPLACE INTO api_configs (platform, key_name, key_value, updated_at)
                    VALUES (:platform, :key_name, :key_value, CURRENT_TIMESTAMP)";
            
            try {
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    ':platform' => $platform,
                    ':key_name' => $key,
                    ':key_value' => $value
                ]);
            } catch (PDOException $e) {
                throw new Exception("Failed to save config: " . $e->getMessage());
            }
        }
    }

    public function getConfig(string $platform): array {
        $sql = "SELECT key_name, key_value FROM api_configs WHERE platform = :platform";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':platform' => $platform]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $config = [];
            foreach ($results as $row) {
                $config[$row['key_name']] = $row['key_value'];
            }
            return $config;
        } catch (PDOException $e) {
            throw new Exception("Failed to get config: " . $e->getMessage());
        }
    }

    public function getAllConfigs(): array {
        $configs = [];
        foreach ($this->configFiles as $platform => $file) {
            try {
                $dbConfig = $this->getConfig($platform);
                $fileConfig = require $file;
                $configs[$platform] = array_merge($fileConfig, $dbConfig);
            } catch (Exception $e) {
                $configs[$platform] = [];
            }
        }
        return $configs;
    }

    public function testConnection(string $platform): bool {
        $config = $this->getConfig($platform);
        
        switch ($platform) {
            case 'openai':
                return $this->testOpenAI($config['api_key'] ?? '');
            case 'oauth':
                // Add OAuth connection tests for each platform
                return true;
            default:
                throw new Exception("Unsupported platform: $platform");
        }
    }

    private function testOpenAI(string $apiKey): bool {
        $ch = curl_init('https://api.openai.com/v1/models');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }
}
?>
