<?php
require_once __DIR__ . '/../config/init.php';

// Only administrators can delete projects
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectId = $_POST['id'] ?? 0;
    
    if ($projectId) {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$projectId]);
    }
}

header('Location: /projects');
exit;
