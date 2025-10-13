    <header class="main-header">
        <div class="header-container">
            <div class="header-left">
                <h1 class="site-title">EU Projects in MNE</h1>
            </div>
            <nav class="main-nav">
                <a href="/dashboard.php">Dashboard</a>
                <a href="/projects.php">Projects</a>
                <?php if (isAdmin()): ?>
                    <a href="/users.php">Users</a>
                    <a href="/projects-import.php">Import</a>
                <?php endif; ?>
                <a href="/profile.php">My Profile</a>
                <a href="/logout.php" class="logout-link">Logout</a>
            </nav>
            <div class="user-info">
                <span>Welcome, <?= htmlspecialchars($_SESSION['full_name'] ?? 'User') ?></span>
            </div>
        </div>
    </header>
