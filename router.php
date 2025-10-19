<?php
// Built-in PHP server router for Replit development
// This handles routing for the built-in PHP development server
//
// IMPORTANT: Clean URLs work differently in development vs production:
// - PRODUCTION (websupport.sk): Apache + .htaccess handle clean URLs automatically
// - DEVELOPMENT (Replit): This router internally rewrites clean URLs to .php files
//
// This ensures the app works in both environments without code changes.

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve static files directly from public directory (CSS, JS, images, JSON, etc.)
$publicFile = __DIR__ . '/public' . $uri;
if ($uri !== '/' && file_exists($publicFile) && !is_dir($publicFile)) {
    // Get file extension
    $extension = pathinfo($publicFile, PATHINFO_EXTENSION);
    
    // Set appropriate content type
    $contentTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
    ];
    
    if (isset($contentTypes[$extension])) {
        header('Content-Type: ' . $contentTypes[$extension]);
    }
    
    readfile($publicFile);
    exit;
}

// Serve .php files from public directory
if (preg_match('/\.php$/', $uri)) {
    $phpFile = __DIR__ . '/public' . $uri;
    if (file_exists($phpFile)) {
        chdir(__DIR__ . '/public');
        require $phpFile;
        exit;
    }
}

// Map clean URLs to .php files for Replit development
// Instead of redirecting, we internally rewrite the URI to avoid redirect loops
$cleanUrlMap = [
    '/home' => '/home.php',
    '/public' => '/public.php',
    '/login' => '/login.php',
    '/logout' => '/logout.php',
    '/dashboard' => '/dashboard.php',
    '/users' => '/users.php',
    '/users/create' => '/user-add.php',
    '/users/edit' => '/user-edit.php',
    '/users/delete' => '/users.php',  // Delete handled by POST to users.php
    '/profile' => '/profile.php',
    '/projects' => '/projects.php',
    '/projects/create' => '/project-add.php',
    '/projects/edit' => '/project-edit.php',
    '/projects/delete' => '/project-delete.php',
    '/projects/view' => '/project-view.php',
    '/import' => '/projects-import.php',
    '/public-view' => '/public-project.php',
];

// Check if this is a clean URL that needs internal rewriting
$path = strtok($uri, '?'); // Get path without query string
if (isset($cleanUrlMap[$path])) {
    // Internally rewrite the URI to the .php file
    $phpFile = $cleanUrlMap[$path];
    $targetFile = __DIR__ . '/public' . $phpFile;
    
    if (file_exists($targetFile)) {
        // Update SERVER variables to reflect the rewritten path
        $_SERVER['SCRIPT_NAME'] = $phpFile;
        $_SERVER['SCRIPT_FILENAME'] = $targetFile;
        $_SERVER['PHP_SELF'] = $phpFile;
        
        chdir(__DIR__ . '/public');
        require $targetFile;
        exit;
    }
}

// Fallback to index.php for root or not found
chdir(__DIR__ . '/public');
require __DIR__ . '/public/index.php';
