<?php
session_start();

// Include database configuration
require_once __DIR__ . '/database.php';

// Initialize database tables
function initDatabase() {
    $pdo = getDbConnection();
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        full_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(50) NOT NULL DEFAULT 'Editor',
        is_active BOOLEAN DEFAULT true,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'Administrator'");
    $stmt->execute();
    $adminCount = $stmt->fetchColumn();
    
    // Create default admin if none exists
    if ($adminCount == 0) {
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, is_active) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            'Administrator',
            'admin@euprojects.me',
            password_hash('admin123', PASSWORD_DEFAULT),
            'Administrator',
            true
        ]);
    }
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper function to check if user is administrator
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'Administrator';
}

// Helper function to get current user
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT id, full_name, email, role, is_active FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Redirect to login if not authenticated
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit;
    }
}

// Redirect to dashboard if not admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: /dashboard.php');
        exit;
    }
}

// Initialize database on every page load (only creates if doesn't exist)
initDatabase();
