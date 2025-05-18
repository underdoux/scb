<?php
require_once __DIR__ . '/../db/dbConnection.php';

try {
    $pdo = getDBConnection();

    // Create posts table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            content TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Create schedules table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS schedules (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            post_id INTEGER NOT NULL,
            scheduled_time DATETIME NOT NULL,
            status TEXT NOT NULL DEFAULT 'pending',
            platform TEXT NOT NULL,
            FOREIGN KEY (post_id) REFERENCES posts(id)
        )
    ");

    echo "Posts and schedules tables created successfully!\n";

} catch (Exception $e) {
    echo "Error creating tables: " . $e->getMessage() . "\n";
}
?>
