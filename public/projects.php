<?php
require_once __DIR__ . '/../config/init.php';
requireLogin();

$pdo = getDbConnection();

// Pagination settings
$itemsPerPage = 20;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $itemsPerPage;

// Get total count
$stmt = $pdo->query("SELECT COUNT(*) FROM projects");
$totalProjects = $stmt->fetchColumn();

// Calculate total pages
$totalPages = ceil($totalProjects / $itemsPerPage);

// Get projects for current page
$stmt = $pdo->prepare("SELECT * FROM projects ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->execute([$itemsPerPage, $offset]);
$projects = $stmt->fetchAll();

// Calculate showing range
$showingFrom = $totalProjects > 0 ? $offset + 1 : 0;
$showingTo = min($offset + $itemsPerPage, $totalProjects);
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
                    <span>Showing <strong><?= $showingFrom ?>-<?= $showingTo ?></strong> of <strong><?= $totalProjects ?></strong> projects</span>
                </div>
                <div class="actions">
                    <?php if (isAdmin()): ?>
                        <a href="/projects-import" class="btn btn-secondary">Import from Excel</a>
                        <a href="/project-add" class="btn btn-primary">Add New Project</a>
                    <?php else: ?>
                        <a href="/project-add" class="btn btn-primary">Add New Project</a>
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
                                        <a href="/project-view?id=<?= $project['id'] ?>" class="btn btn-small">View</a>
                                        <a href="/project-edit?id=<?= $project['id'] ?>" class="btn btn-small">Edit</a>
                                        <?php if (isAdmin()): ?>
                                            <form method="POST" action="/project-delete" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this project?');">
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
                
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=1" class="pagination-btn">First</a>
                            <a href="?page=<?= $page - 1 ?>" class="pagination-btn">Previous</a>
                        <?php endif; ?>
                        
                        <div class="pagination-numbers">
                            <?php
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);
                            
                            for ($i = $startPage; $i <= $endPage; $i++):
                            ?>
                                <a href="?page=<?= $i ?>" class="pagination-number <?= $i === $page ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1 ?>" class="pagination-btn">Next</a>
                            <a href="?page=<?= $totalPages ?>" class="pagination-btn">Last</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
