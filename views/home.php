<?php
require_once __DIR__ . '/../middleware/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | My PHP Project</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="/">Home</a>
            <a href="/about">About</a>
            <?php if (isAuthenticated()): ?>
                <span class="user-info">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                <a href="/dashboard">Dashboard</a>
                <a href="/generate-content-ui">Generate Content</a>
                <a href="/logout" class="logout-btn">Logout</a>
            <?php else: ?>
                <a href="/login">Login</a>
                <a href="/register">Register</a>
            <?php endif; ?>
        </nav>
        <h1>Welcome to My PHP Project</h1>
    </header>
    <main>
        <div class="dashboard-content">
            <p>This is the home page of our PHP project.</p>
            
            <?php if (!isAuthenticated()): ?>
                <div class="dashboard-section">
                    <h2>Get Started</h2>
                    <p>Please <a href="/login">login</a> or <a href="/register">register</a> to access your dashboard.</p>
                </div>
            <?php endif; ?>

            <!-- Attractive sample image from Pexels -->
            <img src="https://images.pexels.com/photos/414612/pexels-photo-414612.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=750&w=1260" alt="Modern Office">
        </div>
    </main>
    <footer>
        <p>© 2023 My PHP Project</p>
    </footer>
</body>
</html>
