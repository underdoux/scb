<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

requireAuth();
$username = htmlspecialchars($_SESSION['username']);
$isUserAdmin = $_SESSION['role'] === 'admin';

require_once __DIR__ . '/../db/dbConnection.php';

$pdo = getDBConnection();

// Handle form submission for new post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $content = trim($_POST['content']);
    if ($content !== '') {
        $stmt = $pdo->prepare("INSERT INTO posts (user_id, content) VALUES (:user_id, :content)");
        $stmt->execute([':user_id' => $_SESSION['user_id'], ':content' => $content]);
        $_SESSION['success'] = 'Post created successfully.';
        header('Location: /posts');
        exit;
    } else {
        $_SESSION['error'] = 'Content cannot be empty.';
    }
}

// Fetch posts for the user
$stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = :user_id ORDER BY created_at DESC");
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manage Posts | My PHP Project</title>
    <link rel="stylesheet" href="/css/style.css" />
</head>
<body>
    <header>
        <nav>
            <a href="/">Home</a>
            <a href="/dashboard">Dashboard</a>
            <a href="/posts" class="active">Manage Posts</a>
            <a href="/logout" class="logout-btn">Logout</a>
        </nav>
        <h1>Manage Posts</h1>
    </header>
    <main>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <section>
            <h2>Create New Post</h2>
            <form method="POST" action="/posts">
                <textarea name="content" rows="4" cols="50" placeholder="Enter post content here..." required></textarea>
                <br />
                <button type="submit" class="button primary">Create Post</button>
            </form>
        </section>

        <section>
            <h2>Your Posts</h2>
            <?php if (count($posts) === 0): ?>
                <p>No posts found.</p>
            <?php else: ?>
                <ul class="posts-list">
                    <?php foreach ($posts as $post): ?>
                        <li>
                            <div class="post-content"><?php echo nl2br(htmlspecialchars($post['content'])); ?></div>
                            <div class="post-meta">Created at: <?php echo htmlspecialchars($post['created_at']); ?></div>
                            <!-- Edit and Delete buttons can be added here -->
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </main>
    <footer>
        <p>© 2023 My PHP Project</p>
    </footer>
</body>
</html>
