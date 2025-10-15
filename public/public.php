<?php
require_once __DIR__ . '/../config/database.php';

$pdo = getDbConnection();

// Get filter values from GET parameters
$filterSector = isset($_GET['sector']) ? trim($_GET['sector']) : '';
$filterMunicipality = isset($_GET['municipality']) ? trim($_GET['municipality']) : '';
$filterProgram = isset($_GET['program']) ? trim($_GET['program']) : '';
$filterStartYear = isset($_GET['start_year']) ? trim($_GET['start_year']) : '';
$filterEndYear = isset($_GET['end_year']) ? trim($_GET['end_year']) : '';
$filterBeneficiary = isset($_GET['beneficiary']) ? trim($_GET['beneficiary']) : '';
$filterStatus = isset($_GET['status']) ? trim($_GET['status']) : '';

// Get distinct values for filters
$sectors = [];
$stmt = $pdo->query("SELECT DISTINCT TRIM(sector_1) as sector FROM projects WHERE sector_1 IS NOT NULL AND TRIM(sector_1) != '' UNION SELECT DISTINCT TRIM(sector_2) as sector FROM projects WHERE sector_2 IS NOT NULL AND TRIM(sector_2) != '' ORDER BY sector");
while ($row = $stmt->fetch()) {
    if (!empty($row['sector'])) {
        $sectors[] = $row['sector'];
    }
}

$municipalities = [];
$stmt = $pdo->query("SELECT DISTINCT TRIM(municipality) as municipality FROM projects WHERE municipality IS NOT NULL AND TRIM(municipality) != '' ORDER BY municipality");
while ($row = $stmt->fetch()) {
    $municipalities[] = $row['municipality'];
}

$programs = [];
$stmt = $pdo->query("SELECT DISTINCT TRIM(programme) as programme FROM projects WHERE programme IS NOT NULL AND TRIM(programme) != '' ORDER BY programme");
while ($row = $stmt->fetch()) {
    $programs[] = $row['programme'];
}

$beneficiaries = [];
$stmt = $pdo->query("SELECT DISTINCT TRIM(contracting_party) as contracting_party FROM projects WHERE contracting_party IS NOT NULL AND TRIM(contracting_party) != '' ORDER BY contracting_party");
while ($row = $stmt->fetch()) {
    $beneficiaries[] = $row['contracting_party'];
}

// Get distinct years from start_date and end_date
$years = [];
$stmt = $pdo->query("SELECT DISTINCT EXTRACT(YEAR FROM start_date)::int as year FROM projects WHERE start_date IS NOT NULL UNION SELECT DISTINCT EXTRACT(YEAR FROM end_date)::int as year FROM projects WHERE end_date IS NOT NULL ORDER BY 1");
while ($row = $stmt->fetch()) {
    $years[] = $row['year'];
}

// Build WHERE clause based on filters
$where = [];
$params = [];

if (!empty($filterSector)) {
    $where[] = "(sector_1 = ? OR sector_2 = ?)";
    $params[] = $filterSector;
    $params[] = $filterSector;
}

if (!empty($filterMunicipality)) {
    $where[] = "municipality = ?";
    $params[] = $filterMunicipality;
}

if (!empty($filterProgram)) {
    $where[] = "programme = ?";
    $params[] = $filterProgram;
}

if (!empty($filterStartYear)) {
    $where[] = "EXTRACT(YEAR FROM start_date) = ?";
    $params[] = $filterStartYear;
}

if (!empty($filterEndYear)) {
    $where[] = "EXTRACT(YEAR FROM end_date) = ?";
    $params[] = $filterEndYear;
}

if (!empty($filterBeneficiary)) {
    $where[] = "contracting_party = ?";
    $params[] = $filterBeneficiary;
}

if ($filterStatus === 'ongoing') {
    $where[] = "(end_date IS NULL OR end_date >= CURRENT_DATE)";
} elseif ($filterStatus === 'completed') {
    $where[] = "end_date < CURRENT_DATE";
}

// Build final query
$sql = "SELECT * FROM projects";
if (!empty($where)) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll();

// Calculate statistics
$totalProjects = count($projects);
$totalFunding = 0;
$ongoingCount = 0;
$completedCount = 0;

foreach ($projects as $project) {
    if ($project['eu_contribution_mne']) {
        $totalFunding += $project['eu_contribution_mne'];
    }
    
    if (!$project['end_date'] || strtotime($project['end_date']) >= time()) {
        $ongoingCount++;
    } else {
        $completedCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EU Projects in Montenegro - Public Dashboard</title>
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
        
        .dashboard-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 280px 1fr 350px;
            gap: 1.5rem;
        }
        
        .filters-section {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        
        .filters-section h2 {
            color: #003399;
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
        }
        
        .filter-group {
            margin-bottom: 1.5rem;
        }
        
        .filter-group label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .filter-group select {
            width: 100%;
            padding: 0.6rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        
        .filter-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-top: 1.5rem;
        }
        
        .map-section {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .map-placeholder {
            background: linear-gradient(135deg, #e6f0ff 0%, #f8f9fa 100%);
            border: 2px dashed #003399;
            border-radius: 8px;
            height: 500px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: #003399;
        }
        
        .map-placeholder h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .results-section {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .statistics {
            margin-bottom: 1.5rem;
        }
        
        .stat-box {
            background: #e6f0ff;
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 0.75rem;
            border-left: 4px solid #003399;
        }
        
        .stat-box h4 {
            color: #666;
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
        }
        
        .stat-box .value {
            color: #003399;
            font-size: 1.3rem;
            font-weight: 700;
        }
        
        .projects-list {
            max-height: 600px;
            overflow-y: auto;
        }
        
        .project-card {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: box-shadow 0.3s;
        }
        
        .project-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .project-card h3 {
            color: #003399;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }
        
        .project-card .meta {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 0.5rem;
        }
        
        .project-card .amount {
            color: #FFCC00;
            font-weight: 600;
            background: #003399;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            display: inline-block;
            font-size: 0.9rem;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }
        
        .status-ongoing {
            background-color: #e8f8f0;
            color: #008844;
        }
        
        .status-completed {
            background-color: #e6f0ff;
            color: #003399;
        }
        
        @media (max-width: 1200px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
            
            .filters-section {
                position: relative;
                top: 0;
            }
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
        </div>
    </div>
    
    <div class="dashboard-container">
        <!-- Filters Section -->
        <div class="filters-section">
            <h2>Filters</h2>
            <form method="GET" action="">
                <div class="filter-group">
                    <label for="sector">Sector</label>
                    <select name="sector" id="sector">
                        <option value="">All Sectors</option>
                        <?php foreach ($sectors as $sector): ?>
                            <option value="<?= htmlspecialchars($sector) ?>" <?= $filterSector === $sector ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sector) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="municipality">Municipality</label>
                    <select name="municipality" id="municipality">
                        <option value="">All Municipalities</option>
                        <?php foreach ($municipalities as $municipality): ?>
                            <option value="<?= htmlspecialchars($municipality) ?>" <?= $filterMunicipality === $municipality ? 'selected' : '' ?>>
                                <?= htmlspecialchars($municipality) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="program">Program</label>
                    <select name="program" id="program">
                        <option value="">All Programs</option>
                        <?php foreach ($programs as $program): ?>
                            <option value="<?= htmlspecialchars($program) ?>" <?= $filterProgram === $program ? 'selected' : '' ?>>
                                <?= htmlspecialchars($program) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="start_year">Start Year</label>
                    <select name="start_year" id="start_year">
                        <option value="">All Years</option>
                        <?php foreach ($years as $year): ?>
                            <option value="<?= $year ?>" <?= $filterStartYear == $year ? 'selected' : '' ?>>
                                <?= $year ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="end_year">End Year</label>
                    <select name="end_year" id="end_year">
                        <option value="">All Years</option>
                        <?php foreach ($years as $year): ?>
                            <option value="<?= $year ?>" <?= $filterEndYear == $year ? 'selected' : '' ?>>
                                <?= $year ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="beneficiary">Beneficiary</label>
                    <select name="beneficiary" id="beneficiary">
                        <option value="">All Beneficiaries</option>
                        <?php foreach ($beneficiaries as $beneficiary): ?>
                            <option value="<?= htmlspecialchars($beneficiary) ?>" <?= $filterBeneficiary === $beneficiary ? 'selected' : '' ?>>
                                <?= htmlspecialchars($beneficiary) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="status">Project Status</label>
                    <select name="status" id="status">
                        <option value="">All Projects</option>
                        <option value="ongoing" <?= $filterStatus === 'ongoing' ? 'selected' : '' ?>>Ongoing</option>
                        <option value="completed" <?= $filterStatus === 'completed' ? 'selected' : '' ?>>Completed</option>
                    </select>
                </div>
                
                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="/public.php" class="btn btn-secondary">Reset Filters</a>
                </div>
            </form>
        </div>
        
        <!-- Map Section -->
        <div class="map-section">
            <div class="map-placeholder">
                <h3>📍 Interactive Map</h3>
                <p>Map will be integrated here with geodata</p>
            </div>
        </div>
        
        <!-- Results Section -->
        <div class="results-section">
            <h2 style="color: #003399; margin-bottom: 1rem;">Statistics</h2>
            <div class="statistics">
                <div class="stat-box">
                    <h4>Total Projects</h4>
                    <div class="value"><?= number_format($totalProjects) ?></div>
                </div>
                <div class="stat-box">
                    <h4>Total EU Funding</h4>
                    <div class="value">€<?= number_format($totalFunding, 2) ?></div>
                </div>
                <div class="stat-box">
                    <h4>Ongoing Projects</h4>
                    <div class="value"><?= number_format($ongoingCount) ?></div>
                </div>
                <div class="stat-box">
                    <h4>Completed Projects</h4>
                    <div class="value"><?= number_format($completedCount) ?></div>
                </div>
            </div>
            
            <h2 style="color: #003399; margin-bottom: 1rem;">Projects (<?= $totalProjects ?>)</h2>
            <div class="projects-list">
                <?php if (empty($projects)): ?>
                    <p style="color: #666; text-align: center; padding: 2rem;">No projects found matching your filters.</p>
                <?php else: ?>
                    <?php foreach ($projects as $project): ?>
                        <?php
                        $isOngoing = !$project['end_date'] || strtotime($project['end_date']) >= time();
                        ?>
                        <div class="project-card">
                            <h3>
                                <?= htmlspecialchars($project['contract_title'] ?? 'Untitled Project') ?>
                                <span class="status-badge <?= $isOngoing ? 'status-ongoing' : 'status-completed' ?>">
                                    <?= $isOngoing ? 'Ongoing' : 'Completed' ?>
                                </span>
                            </h3>
                            <div class="meta">
                                <strong>Programme:</strong> <?= htmlspecialchars($project['programme'] ?? 'N/A') ?><br>
                                <strong>Municipality:</strong> <?= htmlspecialchars($project['municipality'] ?? 'N/A') ?><br>
                                <strong>Period:</strong> 
                                <?= $project['start_date'] ? date('Y', strtotime($project['start_date'])) : 'N/A' ?> - 
                                <?= $project['end_date'] ? date('Y', strtotime($project['end_date'])) : 'Ongoing' ?>
                            </div>
                            <?php if ($project['eu_contribution_mne']): ?>
                                <div class="amount">€<?= number_format($project['eu_contribution_mne'], 2) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
