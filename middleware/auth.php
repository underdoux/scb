<?php
function isAuthenticated(): bool {
    return isset($_SESSION['user_id']);
}

function requireAuth() {
    if (!isAuthenticated()) {
        header('Location: /login');
        exit();
    }
}

function guestOnly() {
    if (isAuthenticated()) {
        header('Location: /dashboard');
        exit();
    }
}

function requireAdmin() {
    requireAuth(); // First check if user is logged in
    
    require_once __DIR__ . '/../src/UserService.php';
    $userService = new UserService();
    
    if (!$userService->isAdmin($_SESSION['user_id'])) {
        http_response_code(403);
        require_once __DIR__ . '/../views/403.php';
        exit();
    }
}

function isAdmin(): bool {
    if (!isAuthenticated()) {
        return false;
    }
    
    require_once __DIR__ . '/../src/UserService.php';
    $userService = new UserService();
    return $userService->isAdmin($_SESSION['user_id']);
}
?>
