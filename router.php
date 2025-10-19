<?php
// Built-in PHP server router
// This handles routing for the built-in PHP development server
//
// NOTE: This router does NOT support clean URLs in development.
// Clean URLs work in production via Apache .htaccess mod_rewrite.
// In Replit development, use .php extensions (e.g., /home.php, /public.php)

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve static files directly (CSS, JS, images, etc.)
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri) && !is_dir(__DIR__ . '/public' . $uri)) {
    return false;
}

// Serve .php files directly
if (preg_match('/\.php$/', $uri)) {
    return false;
}

// Fallback to index.php for root or not found
chdir(__DIR__ . '/public');
require __DIR__ . '/public/index.php';
