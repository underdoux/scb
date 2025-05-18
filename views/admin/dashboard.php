<?php
requireAdmin();
$username = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | My PHP Project</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <nav>
            <a href="/">Home</a>
            <a href="/admin/dashboard" class="active">Admin Dashboard</a>
            <a href="/admin/users">Manage Users</a>
            <a href="/admin/api-settings">API Settings</a>
            <span class="user-info">Admin: <?php echo $username; ?></span>
            <a href="/logout" class="logout-btn">Logout</a>
        </nav>
        <h1>Admin Dashboard</h1>
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
                <h2><i class="fas fa-users"></i> User Management</h2>
                <p>Manage user accounts and permissions.</p>
                <div class="admin-actions">
                    <a href="/admin/users" class="button">Manage Users</a>
                </div>
            </div>

            <div class="dashboard-section">
                <h2><i class="fas fa-key"></i> API Configuration</h2>
                <p>Configure OAuth and OpenAI API settings for social media integration.</p>
                <div class="admin-actions">
                    <a href="/admin/api-settings" class="button">Configure APIs</a>
                </div>
            </div>

            <div class="dashboard-section">
                <h2><i class="fas fa-cog"></i> System Settings</h2>
                <p>Configure system-wide settings and preferences.</p>
                <div class="admin-actions">
                    <a href="/admin/settings" class="button">System Settings</a>
                </div>
            </div>
        </div>
    </main>
    <footer>
        <p>© 2023 My PHP Project - Admin Area</p>
    </footer>
</body>
</html>
