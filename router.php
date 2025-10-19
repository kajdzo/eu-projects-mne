<?php
// Built-in PHP server router
// This handles routing for the built-in PHP development server
// Supports clean URLs (e.g., /home instead of /home.php)

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve static files directly (CSS, JS, images, etc.)
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri) && !is_dir(__DIR__ . '/public' . $uri)) {
    return false;
}

// Clean URLs - map extensionless paths to .php files
if ($uri !== '/' && !preg_match('/\.php$/', $uri)) {
    $phpFile = '/public' . $uri . '.php';
    $fullPath = __DIR__ . $phpFile;
    if (file_exists($fullPath)) {
        // Set server variables as if the .php file was requested
        $_SERVER['SCRIPT_FILENAME'] = $fullPath;
        $_SERVER['SCRIPT_NAME'] = $phpFile;
        $_SERVER['PHP_SELF'] = $phpFile;
        // Change to public directory so relative paths work
        chdir(__DIR__ . '/public');
        // Include the file and stop processing
        include $fullPath;
        exit;
    }
}

// Serve .php files directly
if (preg_match('/\.php$/', $uri)) {
    return false;
}

// Fallback to index.php for root or not found
chdir(__DIR__ . '/public');
require __DIR__ . '/public/index.php';
