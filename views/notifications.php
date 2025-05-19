<?php
requireAuth();
$username = htmlspecialchars($_SESSION['username']);
$isUserAdmin = $_SESSION['role'] === 'admin';

require_once __DIR__ . '/../db/dbConnection.php';

$pdo = getDBConnection();

// Fetch notifications for the user, ordered by most recent first
$stmt = $pdo->prepare("
    SELECT * FROM notifications 
    WHERE user_id = :user_id 
    ORDER BY created_at DESC 
    LIMIT 50
");
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | My PHP Project</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="/">Home</a>
            <a href="/dashboard">Dashboard</a>
            <a href="/posts">Manage Posts</a>
            <a href="/schedules">Manage Schedules</a>
            <a href="/notifications" class="active">Notifications</a>
            <a href="/logout" class="logout-btn">Logout</a>
        </nav>
        <h1>Notifications</h1>
    </header>
    <main>
        <div class="notifications-container">
            <?php if (count($notifications) === 0): ?>
                <p>No notifications found.</p>
            <?php else: ?>
                <ul class="notifications-list">
                    <?php foreach ($notifications as $notification): ?>
                        <li class="notification-item notification-<?php echo htmlspecialchars($notification['type']); ?>">
                            <div class="notification-message">
                                <?php echo htmlspecialchars($notification['message']); ?>
                            </div>
                            <div class="notification-meta">
                                <?php echo htmlspecialchars($notification['created_at']); ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </main>
    <footer>
        <p>© 2023 My PHP Project</p>
    </footer>

    <style>
        .notifications-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }

        .notifications-list {
            list-style: none;
            padding: 0;
        }

        .notification-item {
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            border-left: 4px solid #ccc;
        }

        .notification-success {
            background-color: #e8f5e9;
            border-left-color: #4caf50;
        }

        .notification-failure {
            background-color: #ffebee;
            border-left-color: #f44336;
        }

        .notification-token_issue {
            background-color: #fff3e0;
            border-left-color: #ff9800;
        }

        .notification-message {
            font-size: 1rem;
            margin-bottom: 5px;
        }

        .notification-meta {
            font-size: 0.8rem;
            color: #666;
        }
    </style>
</body>
</html>
