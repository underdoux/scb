<?php
require_once __DIR__ . '/../db/dbConnection.php';

try {
    $pdo = getDBConnection();

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS notifications (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            message TEXT NOT NULL,
            type TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    echo "Notifications table created successfully!\n";

} catch (Exception $e) {
    echo "Error creating notifications table: " . $e->getMessage() . "\n";
}
?>
