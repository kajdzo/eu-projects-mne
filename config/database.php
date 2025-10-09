<?php
// Database configuration
function getDbConnection() {
    $dbUrl = getenv('DATABASE_URL');
    
    if (!$dbUrl) {
        die("DATABASE_URL environment variable not set");
    }
    
    // Parse DATABASE_URL
    $dbParts = parse_url($dbUrl);
    
    $host = $dbParts['host'];
    $port = $dbParts['port'] ?? 5432;
    $dbname = ltrim($dbParts['path'], '/');
    $user = $dbParts['user'];
    $password = $dbParts['pass'];
    
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
