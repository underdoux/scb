<?php
requireAuth();
$username = htmlspecialchars($_SESSION['username']);
$isUserAdmin = $_SESSION['role'] === 'admin';

// Get connected social accounts
require_once __DIR__ . '/../src/OAuthService.php';
$oauthService = new OAuthService();
$platforms = ['facebook', 'twitter', 'linkedin'];
$connectedAccounts = [];

foreach ($platforms as $platform) {
    try {
        $token = $oauthService->getToken($_SESSION['user_id'], $platform);
        $connectedAccounts[$platform] = $token !== null;
    } catch (Exception $e) {
        $connectedAccounts[$platform] = false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | My PHP Project</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <nav>
            <a href="/">Home</a>
            <a href="/about">About</a>
            <a href="/generate-content-ui">Generate Content</a>
            <a href="/posts">Manage Posts</a>
            <a href="/schedules">Manage Schedules</a>
            <a href="/notifications">Notifications</a>
            <?php if ($isUserAdmin): ?>
                <a href="/admin/dashboard">Admin Dashboard</a>
            <?php endif; ?>
            <span class="user-info">
                Welcome, <?php echo $username; ?>
                <?php if ($isUserAdmin): ?>
                    (Admin)
                <?php endif; ?>
            </span>
            <a href="/logout" class="logout-btn">Logout</a>
        </nav>
        <h1>User Dashboard</h1>
    </header>
    <main>
        <div class="dashboard-content">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php 
                    echo htmlspecialchars($_SESSION['error']);
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo htmlspecialchars($_SESSION['success']);
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="dashboard-section">
                <h2>Your Account Details</h2>
                <ul>
                    <li>Username: <?php echo $username; ?></li>
                    <li>User ID: <?php echo $_SESSION['user_id']; ?></li>
                    <li>Role: <?php echo ucfirst($_SESSION['role']); ?></li>
                </ul>
            </div>

            <div class="dashboard-section">
                <h2>Connected Social Media Accounts</h2>
                <div class="social-accounts">
                    <!-- Facebook -->
                    <div class="social-account">
                        <h3><i class="fab fa-facebook"></i> Facebook Pages</h3>
                        <?php if ($connectedAccounts['facebook']): ?>
                            <p class="connected"><i class="fas fa-check-circle"></i> Connected</p>
                            <form action="/oauth/facebook/disconnect" method="POST">
                                <button type="submit" class="button danger">Disconnect</button>
                            </form>
                        <?php else: ?>
                            <p class="not-connected"><i class="fas fa-times-circle"></i> Not Connected</p>
                            <a href="/oauth/facebook/login" class="button">Connect Facebook</a>
                        <?php endif; ?>
                    </div>

                    <!-- Twitter -->
                    <div class="social-account">
                        <h3><i class="fab fa-twitter"></i> Twitter (X)</h3>
                        <?php if ($connectedAccounts['twitter']): ?>
                            <p class="connected"><i class="fas fa-check-circle"></i> Connected</p>
                            <form action="/oauth/twitter/disconnect" method="POST">
                                <button type="submit" class="button danger">Disconnect</button>
                            </form>
                        <?php else: ?>
                            <p class="not-connected"><i class="fas fa-times-circle"></i> Not Connected</p>
                            <a href="/oauth/twitter/login" class="button">Connect Twitter</a>
                        <?php endif; ?>
                    </div>

                    <!-- LinkedIn -->
                    <div class="social-account">
                        <h3><i class="fab fa-linkedin"></i> LinkedIn</h3>
                        <?php if ($connectedAccounts['linkedin']): ?>
                            <p class="connected"><i class="fas fa-check-circle"></i> Connected</p>
                            <form action="/oauth/linkedin/disconnect" method="POST">
                                <button type="submit" class="button danger">Disconnect</button>
                            </form>
                        <?php else: ?>
                            <p class="not-connected"><i class="fas fa-times-circle"></i> Not Connected</p>
                            <a href="/oauth/linkedin/login" class="button">Connect LinkedIn</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($isUserAdmin): ?>
            <div class="dashboard-section">
                <h2>Administrative Access</h2>
                <p>As an administrator, you have access to additional features:</p>
                <div class="admin-actions">
                    <a href="/admin/dashboard" class="button">Go to Admin Dashboard</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
    <footer>
        <p>© 2023 My PHP Project</p>
    </footer>
</body>
</html>
