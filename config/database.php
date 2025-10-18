<?php
// Database configuration
function loadEnv($file = __DIR__ . '/../.env') {
    if (!file_exists($file)) {
        return false;
    }
    
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!array_key_exists($name, $_ENV)) {
            putenv("$name=$value");
            $_ENV[$name] = $value;
        }
    }
    return true;
}

function getDbConnection() {
    loadEnv();
    
    $dbUrl = getenv('DATABASE_URL');
    
    if ($dbUrl) {
        $dbParts = parse_url($dbUrl);
        
        $host = $dbParts['host'];
        $port = $dbParts['port'] ?? 5432;
        $dbname = ltrim($dbParts['path'], '/');
        $user = $dbParts['user'];
        $password = $dbParts['pass'];
    } else {
        $host = getenv('DB_HOST') ?: 'localhost';
        $port = getenv('DB_PORT') ?: 5432;
        $dbname = getenv('DB_NAME');
        $user = getenv('DB_USER');
        $password = getenv('DB_PASSWORD');
        
        if (!$dbname || !$user) {
            die("Database configuration not found. Please create a .env file with DB_HOST, DB_PORT, DB_NAME, DB_USER, and DB_PASSWORD");
        }
    }
    
    try {
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}
