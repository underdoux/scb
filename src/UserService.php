<?php
class UserService {
    private $pdo;
    
    public function __construct() {
        require_once __DIR__ . '/../db/dbConnection.php';
        $this->pdo = getDBConnection();
        $this->initializeTable();
    }
    
    private function initializeTable(): void {
        // Create users table
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT 'user',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        
        try {
            $this->pdo->exec($sql);

            // Create OAuth tokens table
            $sql = "CREATE TABLE IF NOT EXISTS oauth_tokens (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                platform TEXT NOT NULL,
                access_token TEXT NOT NULL,
                refresh_token TEXT,
                expires_at DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE(user_id, platform)
            )";
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            throw new Exception("Failed to create users table: " . $e->getMessage());
        }
    }
    
    public function register(string $username, string $email, string $password, string $role = 'user'): bool {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $hashedPassword,
                ':role' => $role
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // SQLite UNIQUE constraint violation
                throw new Exception("Username or email already exists");
            }
            throw new Exception("Registration failed: " . $e->getMessage());
        }
    }
    
    public function login(string $email, string $password): ?array {
        $sql = "SELECT id, username, email, password, role FROM users WHERE email = :email";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                unset($user['password']); // Don't return the password hash
                return $user;
            }
            return null;
        } catch (PDOException $e) {
            throw new Exception("Login failed: " . $e->getMessage());
        }
    }

    public function isAdmin(int $userId): bool {
        $sql = "SELECT role FROM users WHERE id = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user && $user['role'] === 'admin';
        } catch (PDOException $e) {
            throw new Exception("Role check failed: " . $e->getMessage());
        }
    }

    public function createAdmin(string $username, string $email, string $password): bool {
        return $this->register($username, $email, $password, 'admin');
    }

    public function getAllUsers(): PDOStatement {
        $sql = "SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Failed to fetch users: " . $e->getMessage());
        }
    }
}
?>
