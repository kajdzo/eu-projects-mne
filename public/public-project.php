<?php
require_once __DIR__ . '/../config/init.php';

$projectId = $_GET['id'] ?? 0;
$pdo = getDbConnection();

// Get project data
$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->execute([$projectId]);
$project = $stmt->fetch();

if (!$project) {
    header('Location: /public.php');
    exit;
}

// Calculate project status
$status = 'Ongoing';
if ($project['end_date']) {
    $endDate = strtotime($project['end_date']);
    if ($endDate < time()) {
        $status = 'Completed';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($project['contract_title'] ?? 'Project Details') ?> - EU Projects in MNE</title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .public-header {
            background-color: #003399;
            color: white;
            padding: 1.5rem 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .public-header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo-placeholder {
            font-size: 1.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .logo-box {
            width: 60px;
            height: 60px;
            background-color: #FFCC00;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }
        
        .public-nav {
            display: flex;
            gap: 1rem;
        }
        
        .public-nav a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background-color 0.3s;
            font-weight: 500;
            line-height: 1;
            text-align: center;
        }
        
        .public-nav a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .public-nav .btn-home {
            background-color: #FFCC00;
            color: #003399;
            font-weight: 600;
        }
        
        .public-nav .btn-home:hover {
            background-color: #e6b800;
        }
    </style>
</head>
<body>
    <div class="public-header">
        <div class="public-header-container">
            <div class="logo-placeholder">
                <div class="logo-box">🇪🇺</div>
                <div>EU Projects in Montenegro</div>
            </div>
            <div class="public-nav">
                <a href="/home.php" class="btn-home">Home</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="main-content">
            <div class="breadcrumb">
                <a href="/home.php">Home</a> &raquo; 
                <a href="/public.php">Projects</a> &raquo; 
                <span>Project Details</span>
            </div>
            
            <h1><?= htmlspecialchars($project['contract_title'] ?? 'Project Details') ?></h1>
            
            <div class="project-status-badge <?= strtolower($status) ?>">
                <?= $status ?>
            </div>
            
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
                        <?php if ($project['sector_2']): ?>
                            <div class="detail-item">
                                <strong>Sector 2:</strong>
                                <span><?= htmlspecialchars($project['sector_2']) ?></span>
                            </div>
                        <?php endif; ?>
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
                        <?php if ($project['decision_number']): ?>
                            <div class="detail-item">
                                <strong>Decision Number:</strong>
                                <span><?= htmlspecialchars($project['decision_number']) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
                
                <section class="detail-section">
                    <h3>Financial Information</h3>
                    <div class="detail-grid">
                        <?php if ($project['contracted_eu_contribution']): ?>
                            <div class="detail-item">
                                <strong>Contracted EU Contribution:</strong>
                                <span class="amount">€<?= number_format($project['contracted_eu_contribution'], 2) ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($project['eu_contribution_mne']): ?>
                            <div class="detail-item">
                                <strong>EU Contribution for MNE:</strong>
                                <span class="amount">€<?= number_format($project['eu_contribution_mne'], 2) ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($project['eu_contribution_overall']): ?>
                            <div class="detail-item">
                                <strong>EU Contribution Overall:</strong>
                                <span class="amount">€<?= number_format($project['eu_contribution_overall'], 2) ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($project['total_euro_value']): ?>
                            <div class="detail-item">
                                <strong>Total EURO Value:</strong>
                                <span class="amount">€<?= number_format($project['total_euro_value'], 2) ?></span>
                            </div>
                        <?php endif; ?>
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
                
                <?php if ($project['short_description']): ?>
                    <section class="detail-section">
                        <h3>Description</h3>
                        <p><?= nl2br(htmlspecialchars($project['short_description'])) ?></p>
                    </section>
                <?php endif; ?>
                
                <section class="detail-section">
                    <h3>Additional Information</h3>
                    <div class="detail-grid">
                        <?php if ($project['keywords']): ?>
                            <div class="detail-item">
                                <strong>Keywords:</strong>
                                <span><?= htmlspecialchars($project['keywords']) ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($project['project_link']): ?>
                            <div class="detail-item">
                                <strong>Project Link:</strong>
                                <span><a href="<?= htmlspecialchars($project['project_link']) ?>" target="_blank" rel="noopener noreferrer">Visit Project Website →</a></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
            
            <div class="form-actions">
                <a href="/public.php" class="btn btn-primary">← Back to Projects</a>
            </div>
        </div>
    </div>
    
    <footer class="public-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> EU Projects in Montenegro. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
