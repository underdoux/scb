<?php
session_start();
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../src/UserService.php';
require_once __DIR__ . '/../src/OAuthService.php';
require_once __DIR__ . '/../src/OpenAIService.php';
require_once __DIR__ . '/../src/ConfigService.php';

function routeRequest() {
    // Parse the current URI path
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    // Define routes using a simple switch-case
    switch ($uri) {
        case '/':
            require_once __DIR__ . '/../views/home.php';
            break;
        case '/about':
            require_once __DIR__ . '/../views/about.php';
            break;
        case '/register':
            guestOnly();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                handleRegistration();
            } else {
                require_once __DIR__ . '/../views/register.php';
            }
            break;
        case '/login':
            guestOnly();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                handleLogin();
            } else {
                require_once __DIR__ . '/../views/login.php';
            }
            break;
        case '/logout':
            requireAuth();
            handleLogout();
            break;
        case '/dashboard':
            requireAuth();
            require_once __DIR__ . '/../views/dashboard.php';
            break;
        case '/admin/dashboard':
            requireAdmin();
            require_once __DIR__ . '/../views/admin/dashboard.php';
            break;
        case '/admin/users':
            requireAdmin();
            require_once __DIR__ . '/../views/admin/users.php';
            break;
        case '/admin/settings':
            requireAdmin();
            require_once __DIR__ . '/../views/admin/settings.php';
            break;
        case '/admin/api-settings':
            requireAdmin();
            require_once __DIR__ . '/../views/admin/api_settings.php';
            break;
        case '/admin/api-settings/test':
            requireAdmin();
            handleApiTest();
            break;

        case '/generate-content-ui':
            requireAuth();
            require_once __DIR__ . '/../views/generate_content.php';
            break;

        // OAuth Routes
        case '/oauth/facebook/login':
            requireAuth();
            handleOAuthLogin('facebook');
            break;
        case '/oauth/facebook/callback':
            requireAuth();
            handleOAuthCallback('facebook');
            break;
        case '/oauth/twitter/login':
            requireAuth();
            handleOAuthLogin('twitter');
            break;
        case '/oauth/twitter/callback':
            requireAuth();
            handleOAuthCallback('twitter');
            break;
        case '/oauth/linkedin/login':
            requireAuth();
            handleOAuthLogin('linkedin');
            break;
        case '/oauth/linkedin/callback':
            requireAuth();
            handleOAuthCallback('linkedin');
            break;
            
        // OAuth Disconnect Routes
        case '/oauth/facebook/disconnect':
            requireAuth();
            handleOAuthDisconnect('facebook');
            break;
        case '/oauth/twitter/disconnect':
            requireAuth();
            handleOAuthDisconnect('twitter');
            break;
        case '/oauth/linkedin/disconnect':
            requireAuth();
            handleOAuthDisconnect('linkedin');
            break;

        // Content Generation API
        case '/api/generate-content':
            requireAuth();
            handleContentGeneration();
            break;
            
        default:
            http_response_code(404);
            require_once __DIR__ . '/../views/404.php';
            break;
    }
}

function handleRegistration() {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $_SESSION['error'] = "All fields are required";
        header('Location: /register');
        exit();
    }

    if ($password !== $confirmPassword) {
        $_SESSION['error'] = "Passwords do not match";
        header('Location: /register');
        exit();
    }

    try {
        $userService = new UserService();
        $userService->register($username, $email, $password);
        $_SESSION['success'] = "Registration successful! Please login.";
        header('Location: /login');
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: /register');
        exit();
    }
}

function handleLogin() {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Email and password are required";
        header('Location: /login');
        exit();
    }

    try {
        $userService = new UserService();
        $user = $userService->login($email, $password);
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            if ($user['role'] === 'admin') {
                header('Location: /admin/dashboard');
            } else {
                header('Location: /dashboard');
            }
            exit();
        } else {
            $_SESSION['error'] = "Invalid email or password";
            header('Location: /login');
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: /login');
        exit();
    }
}

function handleLogout() {
    session_destroy();
    header('Location: /login');
    exit();
}

function handleApiTest() {
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit();
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $platform = $data['platform'] ?? '';

    if (empty($platform)) {
        http_response_code(400);
        echo json_encode(['error' => 'Platform is required']);
        exit();
    }

    try {
        $configService = new ConfigService();
        $success = $configService->testConnection($platform);
        echo json_encode(['success' => $success]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit();
}

function handleOAuthLogin($platform) {
    try {
        $oauthService = new OAuthService();
        $authUrl = $oauthService->getAuthUrl($platform);
        header('Location: ' . $authUrl);
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = "Failed to initiate OAuth login: " . $e->getMessage();
        header('Location: /dashboard');
        exit();
    }
}

function handleOAuthCallback($platform) {
    $code = $_GET['code'] ?? '';
    $state = $_GET['state'] ?? '';
    
    if (empty($code) || empty($state)) {
        $_SESSION['error'] = "Invalid OAuth callback parameters";
        header('Location: /dashboard');
        exit();
    }

    try {
        $oauthService = new OAuthService();
        $tokenData = $oauthService->handleCallback($platform, $code, $state);
        $oauthService->saveToken($_SESSION['user_id'], $platform, $tokenData);
        
        $_SESSION['success'] = ucfirst($platform) . " account connected successfully!";
        header('Location: /dashboard');
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = "OAuth authentication failed: " . $e->getMessage();
        header('Location: /dashboard');
        exit();
    }
}

function handleOAuthDisconnect($platform) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /dashboard');
        exit();
    }

    try {
        $oauthService = new OAuthService();
        $oauthService->disconnectPlatform($_SESSION['user_id'], $platform);
        $_SESSION['success'] = ucfirst($platform) . " account disconnected successfully!";
    } catch (Exception $e) {
        $_SESSION['error'] = "Failed to disconnect " . ucfirst($platform) . ": " . $e->getMessage();
    }

    header('Location: /dashboard');
    exit();
}

function handleContentGeneration() {
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit();
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $topic = $data['topic'] ?? '';
    $contentType = $data['contentType'] ?? 'engaging';
    $length = $data['length'] ?? 'medium';
    $tone = $data['tone'] ?? 'professional';

    if (empty($topic)) {
        http_response_code(400);
        echo json_encode(['error' => 'Topic is required']);
        exit();
    }

    // Validate content type
    $validContentTypes = ['promotional', 'informative', 'engaging'];
    if (!in_array($contentType, $validContentTypes)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid content type']);
        exit();
    }

    // Validate length
    $validLengths = ['short', 'medium', 'long'];
    if (!in_array($length, $validLengths)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid length']);
        exit();
    }

    // Validate tone
    $validTones = ['professional', 'casual', 'humorous'];
    if (!in_array($tone, $validTones)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid tone']);
        exit();
    }

    try {
        $openAIService = new OpenAIService();
        $userId = $_SESSION['user_id'] ?? null;
        
        // Get remaining requests for rate limit info
        $cacheService = new CacheService();
        $remainingRequests = $userId ? $cacheService->getRemainingRequests($userId) : null;
        
        $content = $openAIService->generateSocialContent($topic, $contentType, $length, $tone, $userId);
        
        // Include remaining requests in the response
        $response = [
            'content' => $content,
            'remaining_requests' => $remainingRequests
        ];
        
        echo json_encode($response);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit();
}
?>
