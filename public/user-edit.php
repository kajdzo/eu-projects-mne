<?php
require_once __DIR__ . '/../config/init.php';
requireLogin();

$userId = $_GET['id'] ?? 0;
$pdo = getDbConnection();

// Check permissions
if (!isAdmin() && $userId != $_SESSION['user_id']) {
    header('Location: /dashboard.php');
    exit;
}

$error = '';
$success = '';

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: /dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Only admins can change role and active status
    if (isAdmin()) {
        $role = $_POST['role'] ?? $user['role'];
        $isActive = isset($_POST['is_active']) ? 1 : 0;
    } else {
        $role = $user['role'];
        $isActive = $user['is_active'];
    }
    
    if ($fullName && $email) {
        // Check if email already exists for another user
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $userId]);
        
        if ($stmt->fetchColumn() > 0) {
            $error = 'Email already exists';
        } else {
            if ($password) {
                // Update with new password
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, password = ?, role = ?, is_active = ? WHERE id = ?");
                $stmt->execute([
                    $fullName,
                    $email,
                    password_hash($password, PASSWORD_DEFAULT),
                    $role,
                    $isActive,
                    $userId
                ]);
            } else {
                // Update without changing password
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, role = ?, is_active = ? WHERE id = ?");
                $stmt->execute([
                    $fullName,
                    $email,
                    $role,
                    $isActive,
                    $userId
                ]);
            }
            
            $success = 'User updated successfully';
            
            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            // Update session if editing own profile
            if ($userId == $_SESSION['user_id']) {
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
            }
        }
    } else {
        $error = 'Please fill in all required fields';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - EU Projects in MNE</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container">
        <div class="main-content">
            <h1>Edit User</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <form method="POST" class="user-form">
                <div class="form-group">
                    <label for="full_name">Full Name: *</label>
                    <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email: *</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password: (leave blank to keep current)</label>
                    <input type="password" id="password" name="password">
                </div>
                
                <?php if (isAdmin()): ?>
                    <div class="form-group">
                        <label for="role">Role: *</label>
                        <select id="role" name="role">
                            <option value="Editor" <?= $user['role'] === 'Editor' ? 'selected' : '' ?>>Editor</option>
                            <option value="Administrator" <?= $user['role'] === 'Administrator' ? 'selected' : '' ?>>Administrator</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_active" <?= $user['is_active'] ? 'checked' : '' ?>>
                            Is Active
                        </label>
                    </div>
                <?php endif; ?>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><span class="btn-text">Update User</span></button>
                    <a href="<?= isAdmin() ? '/users.php' : '/dashboard.php' ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Add spinner to button on form submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const btn = this.querySelector('button[type="submit"]');
            btn.classList.add('loading');
        });
    </script>
</body>
</html>
