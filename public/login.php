<?php
require_once __DIR__ . '/../config/init.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: /dashboard');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($email && $password) {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = true");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            header('Location: /dashboard');
            exit;
        } else {
            $error = 'Invalid email or password';
        }
    } else {
        $error = 'Please fill in all fields';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EU Projects in MNE</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1>EU Projects in MNE</h1>
            <h2>Login</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary"><span class="btn-text">Login</span></button>
            </form>
            
            <div class="login-info">
                <p><small>Default admin: admin@euprojects.me / admin123</small></p>
            </div>
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
