<?php
require_once __DIR__ . '/../src/UserService.php';

try {
    $userService = new UserService();
    
    // Create admin user
    $username = 'admin';
    $email = 'admin@example.com';
    $password = 'admin123'; // Change this in production!
    
    $userService->createAdmin($username, $email, $password);
    echo "Admin user created successfully!\n";
    echo "Username: {$username}\n";
    echo "Email: {$email}\n";
    echo "Password: {$password}\n";
    echo "\nPlease change the password after first login.\n";
    
} catch (Exception $e) {
    echo "Error creating admin user: " . $e->getMessage() . "\n";
}
?>
