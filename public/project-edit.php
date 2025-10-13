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

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("UPDATE projects SET
            financial_framework = ?, programme = ?, type_of_programme = ?, management_mode = ?,
            sector_1 = ?, sector_2 = ?, contract_title = ?, contract_type = ?,
            commitment_year = ?, contract_year = ?, start_date = ?, end_date = ?,
            contract_number = ?, contracting_party = ?, decision_number = ?,
            contracted_eu_contribution = ?, eu_contribution_mne = ?, eu_contribution_overall = ?, total_euro_value = ?,
            municipality = ?, short_description = ?, keywords = ?, project_link = ?,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = ?");
        
        $stmt->execute([
            $_POST['financial_framework'] ?? null,
            $_POST['programme'] ?? null,
            $_POST['type_of_programme'] ?? null,
            $_POST['management_mode'] ?? null,
            $_POST['sector_1'] ?? null,
            $_POST['sector_2'] ?? null,
            $_POST['contract_title'] ?? null,
            $_POST['contract_type'] ?? null,
            $_POST['commitment_year'] ?? null,
            $_POST['contract_year'] ?? null,
            $_POST['start_date'] ?: null,
            $_POST['end_date'] ?: null,
            $_POST['contract_number'] ?? null,
            $_POST['contracting_party'] ?? null,
            $_POST['decision_number'] ?? null,
            $_POST['contracted_eu_contribution'] ?: null,
            $_POST['eu_contribution_mne'] ?: null,
            $_POST['eu_contribution_overall'] ?: null,
            $_POST['total_euro_value'] ?: null,
            $_POST['municipality'] ?? null,
            $_POST['short_description'] ?? null,
            $_POST['keywords'] ?? null,
            $_POST['project_link'] ?? null,
            $projectId
        ]);
        
        $success = 'Project updated successfully!';
        
        // Refresh project data
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$projectId]);
        $project = $stmt->fetch();
    } catch (Exception $e) {
        $error = 'Error updating project: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Project - EU Projects in MNE</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container">
        <div class="main-content">
            <h1>Edit Project</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <form method="POST" class="user-form">
                <h3>Basic Information</h3>
                
                <div class="form-group">
                    <label for="contract_title">Contract Title: *</label>
                    <input type="text" id="contract_title" name="contract_title" value="<?= htmlspecialchars($project['contract_title'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="financial_framework">Financial Framework:</label>
                    <input type="text" id="financial_framework" name="financial_framework" value="<?= htmlspecialchars($project['financial_framework'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="programme">Programme:</label>
                    <input type="text" id="programme" name="programme" value="<?= htmlspecialchars($project['programme'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="type_of_programme">Type of Programme:</label>
                    <input type="text" id="type_of_programme" name="type_of_programme" value="<?= htmlspecialchars($project['type_of_programme'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="management_mode">Management Mode:</label>
                    <input type="text" id="management_mode" name="management_mode" value="<?= htmlspecialchars($project['management_mode'] ?? '') ?>">
                </div>
                
                <h3>Sectors</h3>
                
                <div class="form-group">
                    <label for="sector_1">Sector 1:</label>
                    <input type="text" id="sector_1" name="sector_1" value="<?= htmlspecialchars($project['sector_1'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="sector_2">Sector 2:</label>
                    <input type="text" id="sector_2" name="sector_2" value="<?= htmlspecialchars($project['sector_2'] ?? '') ?>">
                </div>
                
                <h3>Contract Details</h3>
                
                <div class="form-group">
                    <label for="contract_type">Contract Type:</label>
                    <input type="text" id="contract_type" name="contract_type" value="<?= htmlspecialchars($project['contract_type'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="contract_number">Contract Number:</label>
                    <input type="text" id="contract_number" name="contract_number" value="<?= htmlspecialchars($project['contract_number'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="commitment_year">Commitment Year:</label>
                    <input type="text" id="commitment_year" name="commitment_year" value="<?= htmlspecialchars($project['commitment_year'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="contract_year">Contract Year:</label>
                    <input type="text" id="contract_year" name="contract_year" value="<?= htmlspecialchars($project['contract_year'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="start_date">Start Date:</label>
                    <input type="date" id="start_date" name="start_date" value="<?= $project['start_date'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="end_date">End Date:</label>
                    <input type="date" id="end_date" name="end_date" value="<?= $project['end_date'] ?? '' ?>">
                </div>
                
                <h3>Parties & Decision</h3>
                
                <div class="form-group">
                    <label for="contracting_party">Contracting Party:</label>
                    <textarea id="contracting_party" name="contracting_party" rows="2"><?= htmlspecialchars($project['contracting_party'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="decision_number">Decision Number:</label>
                    <input type="text" id="decision_number" name="decision_number" value="<?= htmlspecialchars($project['decision_number'] ?? '') ?>">
                </div>
                
                <h3>Financial Information</h3>
                
                <div class="form-group">
                    <label for="contracted_eu_contribution">Contracted EU Contribution (€):</label>
                    <input type="number" step="0.01" id="contracted_eu_contribution" name="contracted_eu_contribution" value="<?= $project['contracted_eu_contribution'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="eu_contribution_mne">EU Contribution for MNE (€):</label>
                    <input type="number" step="0.01" id="eu_contribution_mne" name="eu_contribution_mne" value="<?= $project['eu_contribution_mne'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="eu_contribution_overall">EU Contribution Overall (€):</label>
                    <input type="number" step="0.01" id="eu_contribution_overall" name="eu_contribution_overall" value="<?= $project['eu_contribution_overall'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="total_euro_value">Total EURO Value (€):</label>
                    <input type="number" step="0.01" id="total_euro_value" name="total_euro_value" value="<?= $project['total_euro_value'] ?? '' ?>">
                </div>
                
                <h3>Location & Description</h3>
                
                <div class="form-group">
                    <label for="municipality">Municipality:</label>
                    <input type="text" id="municipality" name="municipality" value="<?= htmlspecialchars($project['municipality'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="short_description">Short Description:</label>
                    <textarea id="short_description" name="short_description" rows="5"><?= htmlspecialchars($project['short_description'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="keywords">Keywords:</label>
                    <input type="text" id="keywords" name="keywords" value="<?= htmlspecialchars($project['keywords'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="project_link">Project Link:</label>
                    <input type="url" id="project_link" name="project_link" value="<?= htmlspecialchars($project['project_link'] ?? '') ?>">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Project</button>
                    <a href="/projects.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
