<?php
// Built-in PHP server router
// This handles routing for the built-in PHP development server

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve static files directly
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false;
}

// Route all other requests to public directory
$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/public' . $uri;
