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
    
    // Create projects table
    $sqlProjects = "CREATE TABLE IF NOT EXISTS projects (
        id SERIAL PRIMARY KEY,
        financial_framework VARCHAR(255),
        programme VARCHAR(255),
        type_of_programme VARCHAR(255),
        management_mode VARCHAR(255),
        sector_1 TEXT,
        sector_2 TEXT,
        contract_title TEXT,
        contract_type VARCHAR(100),
        commitment_year VARCHAR(10),
        contract_year VARCHAR(10),
        start_date DATE,
        end_date DATE,
        contract_number VARCHAR(100),
        contracting_party TEXT,
        decision_number VARCHAR(100),
        contracted_eu_contribution DECIMAL(15,2),
        eu_contribution_mne DECIMAL(15,2),
        eu_contribution_overall DECIMAL(15,2),
        total_euro_value DECIMAL(15,2),
        municipality VARCHAR(255),
        short_description TEXT,
        keywords TEXT,
        project_link TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sqlProjects);
    
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
