<?php
requireAdmin();
$username = htmlspecialchars($_SESSION['username']);

// Get all users from database
require_once __DIR__ . '/../../src/UserService.php';
$userService = new UserService();
try {
    $stmt = $userService->getAllUsers();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | My PHP Project</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="/">Home</a>
            <a href="/dashboard">User Dashboard</a>
            <a href="/admin/dashboard">Admin Dashboard</a>
            <a href="/admin/users" class="active">Manage Users</a>
            <a href="/admin/settings">Settings</a>
            <span class="user-info">Admin: <?php echo $username; ?></span>
            <a href="/logout" class="logout-btn">Logout</a>
        </nav>
        <h1>User Management</h1>
    </header>
    <main>
        <div class="dashboard-content">
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="dashboard-section">
                <h2>All Users</h2>
                <div class="users-table">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($users)): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                                        <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                        <td class="actions">
                                            <form action="/admin/users/edit" method="POST" class="inline-form">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="button small">Edit</button>
                                            </form>
                                            <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                                <form action="/admin/users/delete" method="POST" class="inline-form">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <button type="submit" class="button small danger">Delete</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="dashboard-section">
                <h2>Create New User</h2>
                <form action="/admin/users/create" method="POST" class="form">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="role">Role:</label>
                        <select id="role" name="role" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <button type="submit" class="button">Create User</button>
                </form>
            </div>
        </div>
    </main>
    <footer>
        <p>© 2023 My PHP Project - Admin Area</p>
    </footer>
</body>
</html>
