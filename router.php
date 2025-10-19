<?php
// Built-in PHP server router for Replit development
// This handles routing for the built-in PHP development server
//
// IMPORTANT: Clean URLs work differently in development vs production:
// - PRODUCTION (websupport.sk): Apache + .htaccess handle clean URLs automatically
// - DEVELOPMENT (Replit): This router redirects clean URLs to .php files
//
// This ensures the app works in both environments without code changes.

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve static files directly (CSS, JS, images, JSON, etc.)
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri) && !is_dir(__DIR__ . '/public' . $uri)) {
    return false;
}

// Serve .php files directly
if (preg_match('/\.php$/', $uri)) {
    return false;
}

// Map clean URLs to .php files for Replit development
// This allows index.php redirects and links to work without causing redirect loops
$cleanUrlMap = [
    '/home' => '/home.php',
    '/public' => '/public.php',
    '/login' => '/login.php',
    '/logout' => '/logout.php',
    '/dashboard' => '/dashboard.php',
    '/users' => '/users.php',
    '/users/create' => '/users/create.php',
    '/users/edit' => '/users/edit.php',
    '/users/delete' => '/users/delete.php',
    '/profile' => '/profile.php',
    '/projects' => '/projects.php',
    '/projects/create' => '/projects/create.php',
    '/projects/edit' => '/projects/edit.php',
    '/projects/delete' => '/projects/delete.php',
    '/projects/view' => '/projects/view.php',
    '/import' => '/import.php',
    '/public-view' => '/public-view.php',
];

// Check if this is a clean URL that needs redirection
$path = strtok($uri, '?'); // Get path without query string
if (isset($cleanUrlMap[$path])) {
    // Get query string if exists
    $queryString = $_SERVER['QUERY_STRING'] ?? '';
    $redirectUrl = $cleanUrlMap[$path] . ($queryString ? '?' . $queryString : '');
    
    header('Location: ' . $redirectUrl, true, 302);
    exit;
}

// Fallback to index.php for root or not found
chdir(__DIR__ . '/public');
require __DIR__ . '/public/index.php';
