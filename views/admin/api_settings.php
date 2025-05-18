<?php
requireAdmin();
require_once __DIR__ . '/../../src/ConfigService.php';

$configService = new ConfigService();
$configs = $configService->getAllConfigs();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['openai'])) {
            $configService->saveConfig('openai', [
                'api_key' => $_POST['openai']['api_key']
            ]);
            $_SESSION['success'] = 'OpenAI settings updated successfully';
        }
        
        if (isset($_POST['oauth'])) {
            $configService->saveConfig('oauth', [
                'facebook_client_id' => $_POST['oauth']['facebook_client_id'],
                'facebook_client_secret' => $_POST['oauth']['facebook_client_secret'],
                'twitter_client_id' => $_POST['oauth']['twitter_client_id'],
                'twitter_client_secret' => $_POST['oauth']['twitter_client_secret'],
                'linkedin_client_id' => $_POST['oauth']['linkedin_client_id'],
                'linkedin_client_secret' => $_POST['oauth']['linkedin_client_secret']
            ]);
            $_SESSION['success'] = 'OAuth settings updated successfully';
        }
        
        header('Location: /admin/api-settings');
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = 'Failed to update settings: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Settings | Admin Dashboard</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <nav>
            <a href="/">Home</a>
            <a href="/admin/dashboard">Admin Dashboard</a>
            <a href="/admin/users">Manage Users</a>
            <a href="/admin/api-settings" class="active">API Settings</a>
            <span class="user-info">Admin: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="/logout" class="logout-btn">Logout</a>
        </nav>
        <h1>API Settings</h1>
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

            <!-- OpenAI Settings -->
            <div class="dashboard-section">
                <h2><i class="fas fa-robot"></i> OpenAI Settings</h2>
                <form action="/admin/api-settings" method="POST" class="form">
                    <div class="form-group">
                        <label for="openai_api_key">API Key:</label>
                        <input type="password" 
                               id="openai_api_key" 
                               name="openai[api_key]" 
                               value="<?php echo htmlspecialchars($configs['openai']['api_key'] ?? ''); ?>"
                               required>
                    </div>
                    <button type="submit" class="button">Save OpenAI Settings</button>
                </form>
            </div>

            <!-- OAuth Settings -->
            <div class="dashboard-section">
                <h2><i class="fas fa-key"></i> OAuth Settings</h2>
                <form action="/admin/api-settings" method="POST" class="form">
                    <!-- Facebook -->
                    <div class="oauth-platform">
                        <h3><i class="fab fa-facebook"></i> Facebook</h3>
                        <div class="callback-url">
                            <label>Callback URL:</label>
                            <div class="url-display">
                                <code>http://localhost:8000/oauth/facebook/callback</code>
                                <button type="button" class="copy-btn" onclick="copyToClipboard(this)" data-url="http://localhost:8000/oauth/facebook/callback">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="facebook_client_id">Client ID:</label>
                            <input type="text" 
                                   id="facebook_client_id" 
                                   name="oauth[facebook_client_id]" 
                                   value="<?php echo htmlspecialchars($configs['oauth']['facebook']['client_id'] ?? ''); ?>"
                                   required>
                        </div>
                        <div class="form-group">
                            <label for="facebook_client_secret">Client Secret:</label>
                            <input type="password" 
                                   id="facebook_client_secret" 
                                   name="oauth[facebook_client_secret]" 
                                   value="<?php echo htmlspecialchars($configs['oauth']['facebook']['client_secret'] ?? ''); ?>"
                                   required>
                        </div>
                    </div>

                    <!-- Twitter -->
                    <div class="oauth-platform">
                        <h3><i class="fab fa-twitter"></i> Twitter</h3>
                        <div class="callback-url">
                            <label>Callback URL:</label>
                            <div class="url-display">
                                <code>http://localhost:8000/oauth/twitter/callback</code>
                                <button type="button" class="copy-btn" onclick="copyToClipboard(this)" data-url="http://localhost:8000/oauth/twitter/callback">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="twitter_client_id">Client ID:</label>
                            <input type="text" 
                                   id="twitter_client_id" 
                                   name="oauth[twitter_client_id]" 
                                   value="<?php echo htmlspecialchars($configs['oauth']['twitter']['client_id'] ?? ''); ?>"
                                   required>
                        </div>
                        <div class="form-group">
                            <label for="twitter_client_secret">Client Secret:</label>
                            <input type="password" 
                                   id="twitter_client_secret" 
                                   name="oauth[twitter_client_secret]" 
                                   value="<?php echo htmlspecialchars($configs['oauth']['twitter']['client_secret'] ?? ''); ?>"
                                   required>
                        </div>
                    </div>

                    <!-- LinkedIn -->
                    <div class="oauth-platform">
                        <h3><i class="fab fa-linkedin"></i> LinkedIn</h3>
                        <div class="callback-url">
                            <label>Callback URL:</label>
                            <div class="url-display">
                                <code>http://localhost:8000/oauth/linkedin/callback</code>
                                <button type="button" class="copy-btn" onclick="copyToClipboard(this)" data-url="http://localhost:8000/oauth/linkedin/callback">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="linkedin_client_id">Client ID:</label>
                            <input type="text" 
                                   id="linkedin_client_id" 
                                   name="oauth[linkedin_client_id]" 
                                   value="<?php echo htmlspecialchars($configs['oauth']['linkedin']['client_id'] ?? ''); ?>"
                                   required>
                        </div>
                        <div class="form-group">
                            <label for="linkedin_client_secret">Client Secret:</label>
                            <input type="password" 
                                   id="linkedin_client_secret" 
                                   name="oauth[linkedin_client_secret]" 
                                   value="<?php echo htmlspecialchars($configs['oauth']['linkedin']['client_secret'] ?? ''); ?>"
                                   required>
                        </div>
                    </div>

                    <button type="submit" class="button">Save OAuth Settings</button>
                </form>
            </div>
        </div>
    </main>
    <footer>
        <p>© 2023 My PHP Project - Admin Area</p>
    </footer>
    <script>
    function copyToClipboard(button) {
        const url = button.getAttribute('data-url');
        navigator.clipboard.writeText(url).then(() => {
            const icon = button.querySelector('i');
            icon.className = 'fas fa-check';
            setTimeout(() => {
                icon.className = 'fas fa-copy';
            }, 2000);
        });
    }
    </script>
</body>
</html>
