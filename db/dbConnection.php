<?php
function getDBConnection(): PDO {
    try {
        $pdo = new PDO("sqlite:" . __DIR__ . "/database.sqlite");
        // Set PDO error mode to exception for robust error handling
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        // In production, log the error instead of displaying it to the user
        die("Database connection failed: " . htmlspecialchars($e->getMessage()));
    }
}
?>
