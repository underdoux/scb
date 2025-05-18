<?php
// Enable error reporting during development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the routing definitions
require_once __DIR__ . '/../routes/routes.php';

// Process the HTTP request using the routing function
try {
    routeRequest();
} catch (Exception $e) {
    // Error handling: Return 500 Internal Server Error and show a safe error message
    http_response_code(500);
    echo 'An error occurred: ' . htmlspecialchars($e->getMessage());
}
?>
