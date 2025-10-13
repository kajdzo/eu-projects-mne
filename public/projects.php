<?php
require_once __DIR__ . '/../config/init.php';
requireLogin();

$pdo = getDbConnection();

// Get all projects
$stmt = $pdo->query("SELECT * FROM projects ORDER BY created_at DESC");
$projects = $stmt->fetchAll();

// Get total count
$stmt = $pdo->query("SELECT COUNT(*) FROM projects");
$totalProjects = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects - EU Projects in MNE</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container">
        <div class="main-content">
            <h1>EU Projects in Montenegro</h1>
            
            <div class="actions-bar">
                <div class="stats">
                    <span>Total Projects: <strong><?= $totalProjects ?></strong></span>
                </div>
                <div class="actions">
                    <?php if (isAdmin()): ?>
                        <a href="/projects-import.php" class="btn btn-secondary">Import from Excel</a>
                        <a href="/project-add.php" class="btn btn-primary">Add New Project</a>
                    <?php else: ?>
                        <a href="/project-add.php" class="btn btn-primary">Add New Project</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (empty($projects)): ?>
                <div class="empty-state">
                    <p>No projects found. <?php if (isAdmin()): ?>Import projects from Excel or add them manually.<?php else: ?>Add your first project.<?php endif; ?></p>
                </div>
            <?php else: ?>
                <div class="projects-table-container">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Contract Title</th>
                                <th>Programme</th>
                                <th>Type</th>
                                <th>Contracting Party</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>EU Contribution (MNE)</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $project): ?>
                                <tr>
                                    <td><?= $project['id'] ?></td>
                                    <td><?= htmlspecialchars($project['contract_title'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($project['programme'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($project['type_of_programme'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($project['contracting_party'] ?? '') ?></td>
                                    <td><?= $project['start_date'] ? date('d M Y', strtotime($project['start_date'])) : '' ?></td>
                                    <td><?= $project['end_date'] ? date('d M Y', strtotime($project['end_date'])) : '' ?></td>
                                    <td><?= $project['eu_contribution_mne'] ? '€' . number_format($project['eu_contribution_mne'], 2) : '' ?></td>
                                    <td class="actions">
                                        <a href="/project-view.php?id=<?= $project['id'] ?>" class="btn btn-small">View</a>
                                        <a href="/project-edit.php?id=<?= $project['id'] ?>" class="btn btn-small">Edit</a>
                                        <?php if (isAdmin()): ?>
                                            <form method="POST" action="/project-delete.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this project?');">
                                                <input type="hidden" name="id" value="<?= $project['id'] ?>">
                                                <button type="submit" class="btn btn-small btn-danger">Delete</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
