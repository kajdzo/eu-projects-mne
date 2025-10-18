<?php
// Built-in PHP server router
// This handles routing for the built-in PHP development server

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve static files directly (CSS, JS, images, etc.)
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri) && !is_dir(__DIR__ . '/public' . $uri)) {
    return false;
}

// Clean URLs - if URI doesn't have .php extension but the .php file exists, serve it
if (!preg_match('/\.php$/', $uri)) {
    $phpFile = __DIR__ . '/public' . $uri . '.php';
    if (file_exists($phpFile)) {
        $_SERVER['SCRIPT_NAME'] = $uri . '.php';
        $_SERVER['PHP_SELF'] = $uri;
        require $phpFile;
        exit;
    }
}

// If URI has .php extension, serve it directly
if (preg_match('/\.php$/', $uri)) {
    $file = __DIR__ . '/public' . $uri;
    if (file_exists($file)) {
        require $file;
        exit;
    }
}

// Fallback to index.php for root or not found
require __DIR__ . '/public/index.php';
