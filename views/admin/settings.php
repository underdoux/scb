<?php
requireAdmin();
$username = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings | My PHP Project</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="/">Home</a>
            <a href="/dashboard">User Dashboard</a>
            <a href="/admin/dashboard">Admin Dashboard</a>
            <a href="/admin/users">Manage Users</a>
            <a href="/admin/settings" class="active">Settings</a>
            <span class="user-info">Admin: <?php echo $username; ?></span>
            <a href="/logout" class="logout-btn">Logout</a>
        </nav>
        <h1>System Settings</h1>
    </header>
    <main>
        <div class="dashboard-content">
            <div class="dashboard-section">
                <h2>Site Configuration</h2>
                <form class="form" action="/admin/settings/update" method="POST">
                    <div class="form-group">
                        <label for="site_name">Site Name:</label>
                        <input type="text" id="site_name" name="site_name" value="My PHP Project">
                    </div>
                    <div class="form-group">
                        <label for="maintenance_mode">Maintenance Mode:</label>
                        <select id="maintenance_mode" name="maintenance_mode">
                            <option value="0">Disabled</option>
                            <option value="1">Enabled</option>
                        </select>
                    </div>
                    <button type="submit" class="button">Save Settings</button>
                </form>
            </div>

            <div class="dashboard-section">
                <h2>System Information</h2>
                <div class="admin-stats">
                    <ul>
                        <li>PHP Version: <?php echo PHP_VERSION; ?></li>
                        <li>Server Software: <?php echo $_SERVER['SERVER_SOFTWARE']; ?></li>
                        <li>Database: SQLite</li>
                    </ul>
                </div>
            </div>
        </div>
    </main>
    <footer>
        <p>© 2023 My PHP Project - Admin Area</p>
    </footer>
</body>
</html>
