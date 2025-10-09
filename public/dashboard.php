<?php
require_once __DIR__ . '/../config/init.php';
requireLogin();

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - EU Projects in MNE</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container">
        <div class="main-content">
            <h1>Dashboard</h1>
            <p>Welcome, <?= htmlspecialchars($user['full_name']) ?>!</p>
            
            <div class="dashboard-stats">
                <div class="stat-card">
                    <h3>Your Role</h3>
                    <p class="stat-value"><?= htmlspecialchars($user['role']) ?></p>
                </div>
                
                <div class="stat-card">
                    <h3>Account Status</h3>
                    <p class="stat-value"><?= $user['is_active'] ? 'Active' : 'Inactive' ?></p>
                </div>
            </div>
            
            <div class="quick-actions">
                <h2>Quick Actions</h2>
                <a href="/profile.php" class="btn btn-primary">Edit My Profile</a>
                <?php if (isAdmin()): ?>
                    <a href="/users.php" class="btn btn-secondary">Manage Users</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
