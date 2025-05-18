<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Forbidden | My PHP Project</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="/">Home</a>
            <a href="/dashboard">Dashboard</a>
            <?php if (isAuthenticated()): ?>
                <span class="user-info">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                <a href="/logout" class="logout-btn">Logout</a>
            <?php endif; ?>
        </nav>
        <h1>403 - Access Forbidden</h1>
    </header>
    <main>
        <div class="dashboard-content">
            <div class="alert alert-error">
                <h2>Access Denied</h2>
                <p>Sorry, you don't have permission to access this page. This area is restricted to administrators only.</p>
            </div>
            <p>Please contact an administrator if you believe you should have access to this page.</p>
            <p><a href="/dashboard">Return to Dashboard</a> or <a href="/">Go to Homepage</a></p>
        </div>
    </main>
    <footer>
        <p>© 2023 My PHP Project</p>
    </footer>
</body>
</html>
