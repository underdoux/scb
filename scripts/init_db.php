<?php
require_once __DIR__ . '/../db/dbConnection.php';

try {
    $pdo = getDBConnection();
    
    // Create oauth_tokens table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS oauth_tokens (
            user_id INTEGER NOT NULL,
            platform TEXT NOT NULL,
            access_token TEXT NOT NULL,
            refresh_token TEXT,
            expires_at DATETIME,
            PRIMARY KEY (user_id, platform)
        )
    ");
    
    echo "Database tables created successfully!\n";
    
} catch (Exception $e) {
    echo "Error initializing database: " . $e->getMessage() . "\n";
}
?>
