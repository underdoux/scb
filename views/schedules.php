<?php
requireAuth();
$username = htmlspecialchars($_SESSION['username']);
$isUserAdmin = $_SESSION['role'] === 'admin';

require_once __DIR__ . '/../db/dbConnection.php';

$pdo = getDBConnection();

// Handle form submission for new schedule
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'], $_POST['scheduled_time'], $_POST['platform'])) {
    $postId = intval($_POST['post_id']);
    $scheduledTime = trim($_POST['scheduled_time']);
    $platform = trim($_POST['platform']);

    if ($postId > 0 && $scheduledTime !== '' && $platform !== '') {
        $stmt = $pdo->prepare("INSERT INTO schedules (post_id, scheduled_time, platform) VALUES (:post_id, :scheduled_time, :platform)");
        $stmt->execute([':post_id' => $postId, ':scheduled_time' => $scheduledTime, ':platform' => $platform]);
        $_SESSION['success'] = 'Schedule created successfully.';
        header('Location: /schedules');
        exit;
    } else {
        $_SESSION['error'] = 'All fields are required.';
    }
}

// Fetch user's posts for selection
$stmt = $pdo->prepare("SELECT id, content FROM posts WHERE user_id = :user_id ORDER BY created_at DESC");
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch schedules joined with posts
$stmt = $pdo->prepare("
    SELECT s.id, s.scheduled_time, s.status, s.platform, p.content
    FROM schedules s
    JOIN posts p ON s.post_id = p.id
    WHERE p.user_id = :user_id
    ORDER BY s.scheduled_time DESC
");
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

$platformOptions = ['facebook', 'instagram', 'twitter', 'linkedin'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manage Schedules | My PHP Project</title>
    <link rel="stylesheet" href="/css/style.css" />
</head>
<body>
    <header>
        <nav>
            <a href="/">Home</a>
            <a href="/dashboard">Dashboard</a>
            <a href="/posts">Manage Posts</a>
            <a href="/schedules" class="active">Manage Schedules</a>
            <a href="/logout" class="logout-btn">Logout</a>
        </nav>
        <h1>Manage Schedules</h1>
    </header>
    <main>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <section>
            <h2>Create New Schedule</h2>
            <form method="POST" action="/schedules">
                <label for="post_id">Select Post:</label>
                <select name="post_id" id="post_id" required>
                    <option value="">-- Select Post --</option>
                    <?php foreach ($posts as $post): ?>
                        <option value="<?php echo $post['id']; ?>"><?php echo htmlspecialchars(substr($post['content'], 0, 50)) . (strlen($post['content']) > 50 ? '...' : ''); ?></option>
                    <?php endforeach; ?>
                </select>
                <br />
                <label for="scheduled_time">Scheduled Time:</label>
                <input type="datetime-local" name="scheduled_time" id="scheduled_time" required />
                <br />
                <label for="platform">Platform:</label>
                <select name="platform" id="platform" required>
                    <option value="">-- Select Platform --</option>
                    <?php foreach ($platformOptions as $platform): ?>
                        <option value="<?php echo $platform; ?>"><?php echo ucfirst($platform); ?></option>
                    <?php endforeach; ?>
                </select>
                <br />
                <button type="submit" class="button primary">Create Schedule</button>
            </form>
        </section>

        <section>
            <h2>Your Schedules</h2>
            <?php if (count($schedules) === 0): ?>
                <p>No schedules found.</p>
            <?php else: ?>
                <table class="schedules-table">
                    <thead>
                        <tr>
                            <th>Post</th>
                            <th>Scheduled Time</th>
                            <th>Platform</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules as $schedule): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(substr($schedule['content'], 0, 50)) . (strlen($schedule['content']) > 50 ? '...' : ''); ?></td>
                                <td><?php echo htmlspecialchars($schedule['scheduled_time']); ?></td>
                                <td><?php echo ucfirst(htmlspecialchars($schedule['platform'])); ?></td>
                                <td><?php echo ucfirst(htmlspecialchars($schedule['status'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>
    <footer>
        <p>© 2023 My PHP Project</p>
    </footer>
</body>
</html>
