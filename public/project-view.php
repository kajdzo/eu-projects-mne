<?php
require_once __DIR__ . '/../config/init.php';
requireLogin();

$projectId = $_GET['id'] ?? 0;
$pdo = getDbConnection();

// Get project data
$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->execute([$projectId]);
$project = $stmt->fetch();

if (!$project) {
    header('Location: /projects.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($project['contract_title'] ?? 'Project Details') ?> - EU Projects in MNE</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container">
        <div class="main-content">
            <h1><?= htmlspecialchars($project['contract_title'] ?? 'Project Details') ?></h1>
            
            <div class="project-details">
                <section class="detail-section">
                    <h3>Basic Information</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <strong>Financial Framework:</strong>
                            <span><?= htmlspecialchars($project['financial_framework'] ?? 'N/A') ?></span>
                        </div>
                        <div class="detail-item">
                            <strong>Programme:</strong>
                            <span><?= htmlspecialchars($project['programme'] ?? 'N/A') ?></span>
                        </div>
                        <div class="detail-item">
                            <strong>Type of Programme:</strong>
                            <span><?= htmlspecialchars($project['type_of_programme'] ?? 'N/A') ?></span>
                        </div>
                        <div class="detail-item">
                            <strong>Management Mode:</strong>
                            <span><?= htmlspecialchars($project['management_mode'] ?? 'N/A') ?></span>
                        </div>
                    </div>
                </section>
                
                <section class="detail-section">
                    <h3>Sectors</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <strong>Sector 1:</strong>
                            <span><?= htmlspecialchars($project['sector_1'] ?? 'N/A') ?></span>
                        </div>
                        <div class="detail-item">
                            <strong>Sector 2:</strong>
                            <span><?= htmlspecialchars($project['sector_2'] ?? 'N/A') ?></span>
                        </div>
                    </div>
                </section>
                
                <section class="detail-section">
                    <h3>Contract Details</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <strong>Contract Type:</strong>
                            <span><?= htmlspecialchars($project['contract_type'] ?? 'N/A') ?></span>
                        </div>
                        <div class="detail-item">
                            <strong>Contract Number:</strong>
                            <span><?= htmlspecialchars($project['contract_number'] ?? 'N/A') ?></span>
                        </div>
                        <div class="detail-item">
                            <strong>Commitment Year:</strong>
                            <span><?= htmlspecialchars($project['commitment_year'] ?? 'N/A') ?></span>
                        </div>
                        <div class="detail-item">
                            <strong>Contract Year:</strong>
                            <span><?= htmlspecialchars($project['contract_year'] ?? 'N/A') ?></span>
                        </div>
                        <div class="detail-item">
                            <strong>Start Date:</strong>
                            <span><?= $project['start_date'] ? date('d M Y', strtotime($project['start_date'])) : 'N/A' ?></span>
                        </div>
                        <div class="detail-item">
                            <strong>End Date:</strong>
                            <span><?= $project['end_date'] ? date('d M Y', strtotime($project['end_date'])) : 'N/A' ?></span>
                        </div>
                    </div>
                </section>
                
                <section class="detail-section">
                    <h3>Parties & Decision</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <strong>Contracting Party:</strong>
                            <span><?= htmlspecialchars($project['contracting_party'] ?? 'N/A') ?></span>
                        </div>
                        <div class="detail-item">
                            <strong>Decision Number:</strong>
                            <span><?= htmlspecialchars($project['decision_number'] ?? 'N/A') ?></span>
                        </div>
                    </div>
                </section>
                
                <section class="detail-section">
                    <h3>Financial Information</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <strong>Contracted EU Contribution:</strong>
                            <span><?= $project['contracted_eu_contribution'] ? '€' . number_format($project['contracted_eu_contribution'], 2) : 'N/A' ?></span>
                        </div>
                        <div class="detail-item">
                            <strong>EU Contribution for MNE:</strong>
                            <span><?= $project['eu_contribution_mne'] ? '€' . number_format($project['eu_contribution_mne'], 2) : 'N/A' ?></span>
                        </div>
                        <div class="detail-item">
                            <strong>EU Contribution Overall:</strong>
                            <span><?= $project['eu_contribution_overall'] ? '€' . number_format($project['eu_contribution_overall'], 2) : 'N/A' ?></span>
                        </div>
                        <div class="detail-item">
                            <strong>Total EURO Value:</strong>
                            <span><?= $project['total_euro_value'] ? '€' . number_format($project['total_euro_value'], 2) : 'N/A' ?></span>
                        </div>
                    </div>
                </section>
                
                <section class="detail-section">
                    <h3>Location</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <strong>Municipality:</strong>
                            <span><?= htmlspecialchars($project['municipality'] ?? 'N/A') ?></span>
                        </div>
                    </div>
                </section>
                
                <section class="detail-section">
                    <h3>Description</h3>
                    <p><?= nl2br(htmlspecialchars($project['short_description'] ?? 'No description available')) ?></p>
                </section>
                
                <section class="detail-section">
                    <h3>Additional Information</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <strong>Keywords:</strong>
                            <span><?= htmlspecialchars($project['keywords'] ?? 'N/A') ?></span>
                        </div>
                        <?php if ($project['project_link']): ?>
                            <div class="detail-item">
                                <strong>Project Link:</strong>
                                <span><a href="<?= htmlspecialchars($project['project_link']) ?>" target="_blank">View Project Page</a></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
            
            <div class="form-actions">
                <a href="/project-edit.php?id=<?= $project['id'] ?>" class="btn btn-primary">Edit Project</a>
                <a href="/projects.php" class="btn btn-secondary">Back to Projects</a>
                <?php if (isAdmin()): ?>
                    <form method="POST" action="/project-delete.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this project?');">
                        <input type="hidden" name="id" value="<?= $project['id'] ?>">
                        <button type="submit" class="btn btn-danger">Delete Project</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
