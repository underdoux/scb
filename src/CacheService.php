<?php
class CacheService {
    private $db;
    private $cacheExpiry = 3600; // Cache for 1 hour
    private $rateLimit = 10; // Requests per minute
    
    public function __construct() {
        require_once __DIR__ . '/../db/dbConnection.php';
        $this->db = getDatabaseConnection();
        $this->initializeCache();
    }
    
    private function initializeCache() {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS content_cache (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                params TEXT NOT NULL,
                content TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS rate_limits (
                user_id INTEGER NOT NULL,
                request_time DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (user_id, request_time)
            )
        ");
    }
    
    public function getCachedContent(array $params): ?array {
        $stmt = $this->db->prepare("
            SELECT content, created_at 
            FROM content_cache 
            WHERE params = :params
        ");
        
        $stmt->bindValue(':params', json_encode($params));
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        
        if ($result) {
            $createdAt = strtotime($result['created_at']);
            if (time() - $createdAt < $this->cacheExpiry) {
                return json_decode($result['content'], true);
            }
            
            // Cache expired, delete it
            $this->db->prepare("
                DELETE FROM content_cache 
                WHERE params = :params
            ")->execute(['params' => json_encode($params)]);
        }
        
        return null;
    }
    
    public function cacheContent(array $params, array $content): void {
        $stmt = $this->db->prepare("
            INSERT INTO content_cache (params, content)
            VALUES (:params, :content)
        ");
        
        $stmt->bindValue(':params', json_encode($params));
        $stmt->bindValue(':content', json_encode($content));
        $stmt->execute();
    }
    
    public function checkRateLimit(int $userId): bool {
        // Clean up old rate limit entries
        $this->db->exec("
            DELETE FROM rate_limits 
            WHERE request_time < datetime('now', '-1 minute')
        ");
        
        // Count requests in the last minute
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM rate_limits 
            WHERE user_id = :user_id 
            AND request_time > datetime('now', '-1 minute')
        ");
        
        $stmt->bindValue(':user_id', $userId);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        
        if ($result['count'] >= $this->rateLimit) {
            return false;
        }
        
        // Log new request
        $stmt = $this->db->prepare("
            INSERT INTO rate_limits (user_id)
            VALUES (:user_id)
        ");
        
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();
        
        return true;
    }
    
    public function getRemainingRequests(int $userId): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM rate_limits 
            WHERE user_id = :user_id 
            AND request_time > datetime('now', '-1 minute')
        ");
        
        $stmt->bindValue(':user_id', $userId);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        
        return $this->rateLimit - $result['count'];
    }
}
