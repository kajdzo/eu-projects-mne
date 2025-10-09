<?php
require_once __DIR__ . '/../config/init.php';
requireAdmin();

$pdo = getDbConnection();
$message = '';

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'toggle_status') {
        $userId = $_POST['user_id'] ?? 0;
        $stmt = $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$userId]);
        $message = 'User status updated successfully';
    } elseif ($action === 'delete') {
        $userId = $_POST['user_id'] ?? 0;
        if ($userId != $_SESSION['user_id']) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $message = 'User deleted successfully';
        }
    }
}

// Get all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - EU Projects in MNE</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container">
        <div class="main-content">
            <h1>User Management</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            
            <div class="actions-bar">
                <a href="/user-add.php" class="btn btn-primary">Add New User</a>
            </div>
            
            <table class="user-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['full_name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><span class="badge badge-<?= $user['role'] === 'Administrator' ? 'admin' : 'editor' ?>"><?= htmlspecialchars($user['role']) ?></span></td>
                            <td><span class="badge badge-<?= $user['is_active'] ? 'active' : 'inactive' ?>"><?= $user['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                            <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
                            <td class="actions">
                                <a href="/user-edit.php?id=<?= $user['id'] ?>" class="btn btn-small">Edit</a>
                                
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <button type="submit" class="btn btn-small btn-warning"><?= $user['is_active'] ? 'Deactivate' : 'Activate' ?></button>
                                </form>
                                
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="btn btn-small btn-danger">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
