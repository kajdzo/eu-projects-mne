    <header class="main-header">
        <div class="header-container">
            <div class="header-left">
                <h1 class="site-title">EU Projects in MNE</h1>
            </div>
            <nav class="main-nav">
                <a href="/dashboard.php">Dashboard</a>
                <a href="/projects.php">Projects</a>
                <a href="/public.php">Public View</a>
                <?php if (isAdmin()): ?>
                    <a href="/users.php">Users</a>
                    <a href="/projects-import.php">Import</a>
                <?php endif; ?>
            </nav>
            <div class="user-dropdown">
                <button class="user-avatar-btn" id="userMenuBtn">
                    <div class="user-avatar">
                        <?= strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <span class="user-name"><?= htmlspecialchars($_SESSION['full_name'] ?? 'User') ?></span>
                    <svg class="dropdown-arrow" width="12" height="12" viewBox="0 0 12 12" fill="none">
                        <path d="M2 4L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
                <div class="user-dropdown-menu" id="userDropdownMenu">
                    <a href="/profile.php" class="dropdown-item">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M8 8C10.21 8 12 6.21 12 4C12 1.79 10.21 0 8 0C5.79 0 4 1.79 4 4C4 6.21 5.79 8 8 8ZM8 10C5.33 10 0 11.34 0 14V16H16V14C16 11.34 10.67 10 8 10Z" fill="currentColor"/>
                        </svg>
                        My Profile
                    </a>
                    <a href="/logout.php" class="dropdown-item logout-item">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M6 14H2V2H6V0H2C0.9 0 0 0.9 0 2V14C0 15.1 0.9 16 2 16H6V14ZM11 12L14 8L11 4V7H5V9H11V12Z" fill="currentColor"/>
                        </svg>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </header>
    
    <script>
        // User dropdown menu toggle
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userDropdownMenu = document.getElementById('userDropdownMenu');
        
        if (userMenuBtn && userDropdownMenu) {
            userMenuBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdownMenu.classList.toggle('show');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!userMenuBtn.contains(e.target) && !userDropdownMenu.contains(e.target)) {
                    userDropdownMenu.classList.remove('show');
                }
            });
        }
    </script>
