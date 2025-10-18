<?php
require_once __DIR__ . '/../config/init.php';
requireAdmin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'Editor';
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    if ($fullName && $email && $password) {
        $pdo = getDbConnection();
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetchColumn() > 0) {
            $error = 'Email already exists';
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, is_active) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $fullName,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
                $role,
                $isActive
            ]);
            $success = 'User created successfully';
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
    <title>Add User - EU Projects in MNE</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container">
        <div class="main-content">
            <h1>Add New User</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?> <a href="/users">Back to users</a></div>
            <?php endif; ?>
            
            <form method="POST" class="user-form">
                <div class="form-group">
                    <label for="full_name">Full Name: *</label>
                    <input type="text" id="full_name" name="full_name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email: *</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password: *</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="role">Role: *</label>
                    <select id="role" name="role">
                        <option value="Editor">Editor</option>
                        <option value="Administrator">Administrator</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_active" checked>
                        Is Active
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><span class="btn-text">Create User</span></button>
                    <a href="/users" class="btn btn-secondary">Cancel</a>
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
