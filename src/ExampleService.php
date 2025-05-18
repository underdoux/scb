<?php
class ExampleService {
    private $pdo;
    
    public function __construct() {
        // Include database connection
        require_once __DIR__ . '/../db/dbConnection.php';
        $this->pdo = getDBConnection();
    }
    
    // Returns a simple greeting message
    public function getGreeting(): string {
        return "Hello from ExampleService!";
    }
    
    // Example method to demonstrate database interaction
    public function createExampleTable(): void {
        $sql = "CREATE TABLE IF NOT EXISTS examples (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        
        try {
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            throw new Exception("Failed to create table: " . $e->getMessage());
        }
    }
}
?>
