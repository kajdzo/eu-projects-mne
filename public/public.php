<?php
require_once __DIR__ . '/../config/database.php';

$pdo = getDbConnection();

// Get filter values from GET parameters
$filterSector = isset($_GET['sector']) ? trim($_GET['sector']) : '';
$filterMunicipality = isset($_GET['municipality']) ? trim($_GET['municipality']) : '';
$filterProgram = isset($_GET['program']) ? trim($_GET['program']) : '';
$filterTypeOfProgramme = isset($_GET['type_of_programme']) ? trim($_GET['type_of_programme']) : '';
$filterStartYear = isset($_GET['start_year']) ? trim($_GET['start_year']) : '';
$filterEndYear = isset($_GET['end_year']) ? trim($_GET['end_year']) : '';
$filterBeneficiary = isset($_GET['beneficiary']) ? trim($_GET['beneficiary']) : '';
$filterStatus = isset($_GET['status']) ? trim($_GET['status']) : '';

// Function to get filter options based on current selections
function getFilterOptions($pdo, $excludeFilter = null) {
    global $filterSector, $filterMunicipality, $filterProgram, $filterTypeOfProgramme, $filterStartYear, $filterEndYear, $filterBeneficiary, $filterStatus;
    
    // Build WHERE clause based on current filters (excluding the one we're updating)
    $where = [];
    $params = [];
    
    if (!empty($filterSector) && $excludeFilter !== 'sector') {
        $where[] = "(UPPER(TRIM(sector_1)) = UPPER(?) OR UPPER(TRIM(sector_2)) = UPPER(?))";
        $params[] = $filterSector;
        $params[] = $filterSector;
    }
    
    if (!empty($filterMunicipality) && $excludeFilter !== 'municipality') {
        $where[] = "UPPER(TRIM(municipality)) = UPPER(?)";
        $params[] = $filterMunicipality;
    }
    
    if (!empty($filterProgram) && $excludeFilter !== 'program') {
        $where[] = "UPPER(TRIM(programme)) = UPPER(?)";
        $params[] = $filterProgram;
    }
    
    if (!empty($filterTypeOfProgramme) && $excludeFilter !== 'type_of_programme') {
        $where[] = "UPPER(TRIM(type_of_programme)) = UPPER(?)";
        $params[] = $filterTypeOfProgramme;
    }
    
    if (!empty($filterStartYear) && $excludeFilter !== 'start_year') {
        $where[] = "EXTRACT(YEAR FROM start_date) = ?";
        $params[] = $filterStartYear;
    }
    
    if (!empty($filterEndYear) && $excludeFilter !== 'end_year') {
        $where[] = "EXTRACT(YEAR FROM end_date) = ?";
        $params[] = $filterEndYear;
    }
    
    if (!empty($filterBeneficiary) && $excludeFilter !== 'beneficiary') {
        $where[] = "UPPER(TRIM(contracting_party)) = UPPER(?)";
        $params[] = $filterBeneficiary;
    }
    
    if ($filterStatus === 'ongoing' && $excludeFilter !== 'status') {
        $where[] = "(end_date IS NULL OR end_date >= CURRENT_DATE)";
    } elseif ($filterStatus === 'completed' && $excludeFilter !== 'status') {
        $where[] = "end_date < CURRENT_DATE";
    }
    
    $whereClause = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";
    
    // Get sectors
    $sectorSql = "SELECT MIN(TRIM(sector)) as sector 
        FROM (
            SELECT sector_1 as sector FROM projects $whereClause " . (!empty($where) ? "AND" : "WHERE") . " sector_1 IS NOT NULL AND TRIM(sector_1) != '' 
            UNION 
            SELECT sector_2 as sector FROM projects $whereClause " . (!empty($where) ? "AND" : "WHERE") . " sector_2 IS NOT NULL AND TRIM(sector_2) != ''
        ) s
        GROUP BY UPPER(TRIM(sector))
        ORDER BY UPPER(TRIM(sector))";
    $stmt = $pdo->prepare($sectorSql);
    $stmt->execute(array_merge($params, $params));
    $sectors = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    
    // Get municipalities
    $municipalitySql = "SELECT MIN(TRIM(municipality)) as municipality FROM projects $whereClause " . (!empty($where) ? "AND" : "WHERE") . " municipality IS NOT NULL AND TRIM(municipality) != '' GROUP BY UPPER(TRIM(municipality)) ORDER BY UPPER(TRIM(municipality))";
    $stmt = $pdo->prepare($municipalitySql);
    $stmt->execute($params);
    $municipalities = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    
    // Get programs
    $programSql = "SELECT MIN(TRIM(programme)) as programme FROM projects $whereClause " . (!empty($where) ? "AND" : "WHERE") . " programme IS NOT NULL AND TRIM(programme) != '' GROUP BY UPPER(TRIM(programme)) ORDER BY UPPER(TRIM(programme))";
    $stmt = $pdo->prepare($programSql);
    $stmt->execute($params);
    $programs = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    
    // Get type of programmes
    $typeSql = "SELECT MIN(TRIM(type_of_programme)) as type_of_programme FROM projects $whereClause " . (!empty($where) ? "AND" : "WHERE") . " type_of_programme IS NOT NULL AND TRIM(type_of_programme) != '' GROUP BY UPPER(TRIM(type_of_programme)) ORDER BY UPPER(TRIM(type_of_programme))";
    $stmt = $pdo->prepare($typeSql);
    $stmt->execute($params);
    $typeOfProgrammes = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    
    // Get beneficiaries
    $beneficiarySql = "SELECT MIN(TRIM(contracting_party)) as contracting_party FROM projects $whereClause " . (!empty($where) ? "AND" : "WHERE") . " contracting_party IS NOT NULL AND TRIM(contracting_party) != '' GROUP BY UPPER(TRIM(contracting_party)) ORDER BY UPPER(TRIM(contracting_party))";
    $stmt = $pdo->prepare($beneficiarySql);
    $stmt->execute($params);
    $beneficiaries = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    
    // Get years
    $yearSql = "SELECT DISTINCT EXTRACT(YEAR FROM start_date)::int as year FROM projects $whereClause " . (!empty($where) ? "AND" : "WHERE") . " start_date IS NOT NULL UNION SELECT DISTINCT EXTRACT(YEAR FROM end_date)::int as year FROM projects $whereClause " . (!empty($where) ? "AND" : "WHERE") . " end_date IS NOT NULL ORDER BY 1";
    $stmt = $pdo->prepare($yearSql);
    $stmt->execute(array_merge($params, $params));
    $years = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    
    return [
        'sectors' => $sectors,
        'municipalities' => $municipalities,
        'programs' => $programs,
        'typeOfProgrammes' => $typeOfProgrammes,
        'beneficiaries' => $beneficiaries,
        'years' => $years
    ];
}

// Handle AJAX request for filter options
if (isset($_GET['get_filter_options'])) {
    header('Content-Type: application/json');
    $options = getFilterOptions($pdo);
    echo json_encode($options);
    exit;
}

// Get distinct values for filters (case-insensitive) based on current selections
$filterOptions = getFilterOptions($pdo);
$sectors = $filterOptions['sectors'];
$municipalities = $filterOptions['municipalities'];
$programs = $filterOptions['programs'];
$typeOfProgrammes = $filterOptions['typeOfProgrammes'];
$beneficiaries = $filterOptions['beneficiaries'];
$years = $filterOptions['years'];

// Build WHERE clause based on filters (case-insensitive)
$where = [];
$params = [];

if (!empty($filterSector)) {
    $where[] = "(UPPER(TRIM(sector_1)) = UPPER(?) OR UPPER(TRIM(sector_2)) = UPPER(?))";
    $params[] = $filterSector;
    $params[] = $filterSector;
}

if (!empty($filterMunicipality)) {
    $where[] = "UPPER(TRIM(municipality)) = UPPER(?)";
    $params[] = $filterMunicipality;
}

if (!empty($filterProgram)) {
    $where[] = "UPPER(TRIM(programme)) = UPPER(?)";
    $params[] = $filterProgram;
}

if (!empty($filterTypeOfProgramme)) {
    $where[] = "UPPER(TRIM(type_of_programme)) = UPPER(?)";
    $params[] = $filterTypeOfProgramme;
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
    $where[] = "UPPER(TRIM(contracting_party)) = UPPER(?)";
    $params[] = $filterBeneficiary;
}

if ($filterStatus === 'ongoing') {
    $where[] = "(end_date IS NULL OR end_date >= CURRENT_DATE)";
} elseif ($filterStatus === 'completed') {
    $where[] = "end_date < CURRENT_DATE";
}

// Pagination
$limit = 20; // Projects per page
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';

// Build count query for statistics
$countSql = "SELECT COUNT(*) as total FROM projects";
if (!empty($where)) {
    $countSql .= " WHERE " . implode(' AND ', $where);
}
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalProjects = (int)$countStmt->fetchColumn();

// Build final query with pagination
$sql = "SELECT * FROM projects";
if (!empty($where)) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll();

// Calculate statistics from all projects (not just current page)
$statsSql = "SELECT 
    SUM(eu_contribution_mne) as total_funding,
    COUNT(CASE WHEN end_date IS NULL OR end_date >= CURRENT_DATE THEN 1 END) as ongoing_count,
    COUNT(CASE WHEN end_date < CURRENT_DATE THEN 1 END) as completed_count
FROM projects";
if (!empty($where)) {
    $statsSql .= " WHERE " . implode(' AND ', array_slice($where, 0, count($where)));
}
$statsParams = array_slice($params, 0, count($params) - 2); // Remove LIMIT and OFFSET
$statsStmt = $pdo->prepare($statsSql);
$statsStmt->execute($statsParams);
$stats = $statsStmt->fetch();

$totalFunding = $stats['total_funding'] ?? 0;
$ongoingCount = $stats['ongoing_count'] ?? 0;
$completedCount = $stats['completed_count'] ?? 0;

// If AJAX request, return JSON
if ($isAjax) {
    header('Content-Type: application/json');
    $projectsHtml = '';
    
    foreach ($projects as $project) {
        $isOngoing = !$project['end_date'] || strtotime($project['end_date']) >= time();
        $projectsHtml .= '<div class="project-card">';
        $projectsHtml .= '<h3>';
        $projectsHtml .= '<a href="/public-project?id=' . $project['id'] . '" class="project-title-link">';
        $projectsHtml .= htmlspecialchars($project['contract_title'] ?? 'Untitled Project');
        $projectsHtml .= '</a>';
        $projectsHtml .= '<span class="status-badge ' . ($isOngoing ? 'status-ongoing' : 'status-completed') . '">';
        $projectsHtml .= $isOngoing ? 'Ongoing' : 'Completed';
        $projectsHtml .= '</span>';
        $projectsHtml .= '</h3>';
        $projectsHtml .= '<div class="meta">';
        $projectsHtml .= '<strong>Programme:</strong> ' . htmlspecialchars($project['programme'] ?? 'N/A') . '<br>';
        $projectsHtml .= '<strong>Municipality:</strong> ' . htmlspecialchars($project['municipality'] ?? 'N/A') . '<br>';
        $projectsHtml .= '<strong>Period:</strong> ';
        $projectsHtml .= ($project['start_date'] ? date('Y', strtotime($project['start_date'])) : 'N/A') . ' - ';
        $projectsHtml .= ($project['end_date'] ? date('Y', strtotime($project['end_date'])) : 'Ongoing');
        $projectsHtml .= '</div>';
        if ($project['eu_contribution_mne']) {
            $projectsHtml .= '<div class="amount">€' . number_format($project['eu_contribution_mne'], 2) . '</div>';
        }
        $projectsHtml .= '</div>';
    }
    
    echo json_encode([
        'html' => $projectsHtml,
        'hasMore' => ($offset + $limit) < $totalProjects,
        'nextOffset' => $offset + $limit
    ]);
    exit;
}

// Calculate if there are more projects to load
$hasMore = ($offset + $limit) < $totalProjects;
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
        
        .statistics-top {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 20px;
        }
        
        .statistics {
            margin-bottom: 1.5rem;
        }
        
        .stat-box {
            background: #e6f0ff;
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
            border-left: none;
            border-top: 4px solid #003399;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .stat-box:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 12px rgba(0,51,153,0.2);
        }
        
        .stat-box h4 {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-box .value {
            color: #003399;
            font-size: 1.8rem;
            font-weight: 700;
        }
        
        .projects-list {
            max-height: 790px;
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
            
            .statistics-top {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 640px) {
            .statistics-top {
                grid-template-columns: 1fr;
            }
        }
        
        /* Map Styles */
        .map-section {
            position: relative;
        }
        
        .map-container {
            position: relative;
            width: 100%;
            height: 600px;
            background-color: #f8f9fa;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .municipality {
            fill: #3498db;
            stroke: #fff;
            stroke-width: 1px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .municipality:hover {
            fill: #2980b9;
            stroke-width: 2px;
        }
        
        .municipality.active {
            fill: #e74c3c;
            stroke-width: 2px;
        }
        
        .map-tooltip {
            position: absolute;
            padding: 8px 12px;
            background: rgba(0, 0, 0, 0.85);
            color: white;
            border-radius: 4px;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 14px;
            z-index: 10;
            font-weight: 500;
        }
        
        .municipality-label {
            font-size: 9px;
            font-weight: 600;
            fill: white;
            text-anchor: middle;
            pointer-events: none;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
            font-family: Arial, sans-serif;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/d3/7.0.0/d3.min.js"></script>
</head>
<body>
    <div class="public-header">
        <div class="public-header-container">
            <div class="logo-placeholder">
                <div class="logo-box">🇪🇺</div>
                <div>EU Projects in Montenegro</div>
            </div>
            <div class="public-nav">
                <a href="/home" class="btn-home">Home</a>
            </div>
        </div>
    </div>
    
    <!-- Statistics Section -->
    <div class="statistics-top">
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
                    <label for="type_of_programme">Type of Programme</label>
                    <select name="type_of_programme" id="type_of_programme">
                        <option value="">All Types</option>
                        <?php foreach ($typeOfProgrammes as $typeOfProgramme): ?>
                            <option value="<?= htmlspecialchars($typeOfProgramme) ?>" <?= $filterTypeOfProgramme === $typeOfProgramme ? 'selected' : '' ?>>
                                <?= htmlspecialchars($typeOfProgramme) ?>
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
                    <a href="/public" class="btn btn-secondary">Reset Filters</a>
                </div>
            </form>
        </div>
        
        <!-- Map Section -->
        <div class="map-section">
            <div class="map-container" id="montenegro-map">
                <div class="map-tooltip"></div>
            </div>
        </div>
        
        <!-- Results Section -->
        <div class="results-section">
            <h2 style="color: #003399; margin-bottom: 0.5rem;">Projects (<?= $totalProjects ?>)</h2>
            <?php if ($totalProjects > 0): ?>
                <div style="margin-bottom: 1rem;">
                    <a href="/public-export?<?= http_build_query(array_filter([
                        'sector' => $filterSector,
                        'municipality' => $filterMunicipality,
                        'program' => $filterProgram,
                        'type_of_programme' => $filterTypeOfProgramme,
                        'start_year' => $filterStartYear,
                        'end_year' => $filterEndYear,
                        'beneficiary' => $filterBeneficiary,
                        'status' => $filterStatus
                    ])) ?>" class="btn btn-secondary" style="background-color: #28a745; color: white;">
                        <span class="btn-text">📥 Export to Excel</span>
                    </a>
                </div>
            <?php endif; ?>
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
                                <a href="/public-project?id=<?= $project['id'] ?>" class="project-title-link">
                                    <?= htmlspecialchars($project['contract_title'] ?? 'Untitled Project') ?>
                                </a>
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
            
            <?php if ($hasMore): ?>
                <div style="text-align: center; margin-top: 2rem;">
                    <button id="loadMoreBtn" class="btn btn-primary" style="min-width: 200px;">
                        <span class="btn-text">Load More Projects</span>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        let currentOffset = <?= $limit ?>;
        let isLoading = false;
        
        // Auto-submit filters on change (AJAX)
        const filterForm = document.querySelector('form');
        const filterSelects = filterForm.querySelectorAll('select');
        
        filterSelects.forEach(select => {
            select.addEventListener('change', function() {
                if (isLoading) return;
                updateFilterOptions();
                loadFilteredResults();
            });
        });
        
        function updateFilterOptions() {
            // Build URL with current filters
            const params = new URLSearchParams();
            filterSelects.forEach(select => {
                if (select.value) {
                    params.set(select.name, select.value);
                }
            });
            params.set('get_filter_options', '1');
            
            // Fetch updated filter options
            fetch('/public?' + params.toString())
                .then(response => response.json())
                .then(data => {
                    // Update each filter dropdown with new options
                    updateDropdown('sector', data.sectors, filterSelects);
                    updateDropdown('municipality', data.municipalities, filterSelects);
                    updateDropdown('program', data.programs, filterSelects);
                    updateDropdown('type_of_programme', data.typeOfProgrammes, filterSelects);
                    updateDropdown('start_year', data.years, filterSelects);
                    updateDropdown('end_year', data.years, filterSelects);
                    updateDropdown('beneficiary', data.beneficiaries, filterSelects);
                })
                .catch(error => {
                    console.error('Error updating filter options:', error);
                });
        }
        
        function updateDropdown(name, options, allSelects) {
            const select = Array.from(allSelects).find(s => s.name === name);
            if (!select) return;
            
            const currentValue = select.value;
            const firstOptionText = select.options[0].text; // "All Sectors", "All Municipalities", etc.
            
            // Clear and rebuild options
            select.innerHTML = '';
            
            // Add default "All..." option
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = firstOptionText;
            select.appendChild(defaultOption);
            
            // Add available options
            options.forEach(optionValue => {
                const option = document.createElement('option');
                option.value = optionValue;
                option.textContent = optionValue;
                if (optionValue === currentValue) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
            
            // If current value is not in the new options, reset to default
            if (currentValue && !options.includes(currentValue)) {
                select.value = '';
            }
        }
        
        function loadFilteredResults() {
            isLoading = true;
            currentOffset = 0; // Reset pagination
            
            // Build URL with current filters
            const params = new URLSearchParams();
            filterSelects.forEach(select => {
                if (select.value) {
                    params.set(select.name, select.value);
                }
            });
            
            // Update browser URL without reload
            const newUrl = '/public' + (params.toString() ? '?' + params.toString() : '');
            window.history.pushState({}, '', newUrl);
            
            // Fetch filtered results
            fetch('/public?' + params.toString())
                .then(response => response.text())
                .then(html => {
                    // Parse the HTML response
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    // Update statistics
                    const newStats = doc.querySelector('.statistics-top');
                    document.querySelector('.statistics-top').innerHTML = newStats.innerHTML;
                    
                    // Update projects list
                    const newProjectsList = doc.querySelector('.projects-list');
                    document.querySelector('.projects-list').innerHTML = newProjectsList.innerHTML;
                    
                    // Update project count in heading
                    const newHeading = doc.querySelector('.results-section h2');
                    const currentHeading = document.querySelector('.results-section h2');
                    if (newHeading && currentHeading) {
                        currentHeading.textContent = newHeading.textContent;
                    }
                    
                    // Update or remove Load More button
                    const oldLoadMoreContainer = document.querySelector('#loadMoreBtn')?.parentElement;
                    const newLoadMoreBtn = doc.querySelector('#loadMoreBtn');
                    
                    if (oldLoadMoreContainer) {
                        oldLoadMoreContainer.remove();
                    }
                    
                    if (newLoadMoreBtn) {
                        const container = document.createElement('div');
                        container.style.textAlign = 'center';
                        container.style.marginTop = '2rem';
                        container.innerHTML = '<button id="loadMoreBtn" class="btn btn-primary" style="min-width: 200px;"><span class="btn-text">Load More Projects</span></button>';
                        document.querySelector('.projects-list').insertAdjacentElement('afterend', container);
                        attachLoadMoreHandler();
                    }
                    
                    currentOffset = <?= $limit ?>;
                    isLoading = false;
                })
                .catch(error => {
                    console.error('Error loading filtered results:', error);
                    isLoading = false;
                });
        }
        
        function attachLoadMoreHandler() {
            const loadMoreBtn = document.getElementById('loadMoreBtn');
            if (!loadMoreBtn) return;
            
            loadMoreBtn.addEventListener('click', function() {
                if (isLoading) return;
                
                // Show loading state
                loadMoreBtn.classList.add('loading');
                loadMoreBtn.disabled = true;
                isLoading = true;
                
                // Build URL with current filters and offset
                const params = new URLSearchParams();
                filterSelects.forEach(select => {
                    if (select.value) {
                        params.set(select.name, select.value);
                    }
                });
                params.set('ajax', '1');
                params.set('offset', currentOffset);
                
                fetch('/public?' + params.toString())
                    .then(response => response.json())
                    .then(data => {
                        // Append new projects to the list
                        const projectsList = document.querySelector('.projects-list');
                        projectsList.insertAdjacentHTML('beforeend', data.html);
                        
                        // Update offset for next load
                        currentOffset = data.nextOffset;
                        
                        // Remove loading state
                        loadMoreBtn.classList.remove('loading');
                        loadMoreBtn.disabled = false;
                        isLoading = false;
                        
                        // Hide button if no more projects
                        if (!data.hasMore) {
                            loadMoreBtn.parentElement.remove();
                        }
                    })
                    .catch(error => {
                        console.error('Error loading more projects:', error);
                        loadMoreBtn.classList.remove('loading');
                        loadMoreBtn.disabled = false;
                        isLoading = false;
                    });
            });
        }
        
        // Initialize Load More handler if button exists
        <?php if ($hasMore): ?>
        attachLoadMoreHandler();
        <?php endif; ?>
    </script>
    
    <footer class="public-footer" style="padding: 2rem 0; margin-top: 3rem; text-align: center; border-top: 1px solid #e5e7eb;">
        <div class="container">
            <p style="font-size: 0.9rem; margin-bottom: 1rem;">
                This website was created and maintained with the financial support of the European Union. 
                Its contents are the sole responsibility of the Europe House and do not necessarily reflect 
                the views of the European Union.
            </p>
            <p style="font-size: 0.85rem;">&copy; <?= date('Y') ?> EU Projects in Montenegro. All rights reserved.</p>
        </div>
    </footer>
    
    <script>
    // Initialize Montenegro Interactive Map
    (function() {
        const mapContainer = document.getElementById('montenegro-map');
        if (!mapContainer) return;
        
        const width = mapContainer.clientWidth;
        const height = mapContainer.clientHeight;
        
        // Create SVG
        const svg = d3.select('#montenegro-map')
            .append('svg')
            .attr('width', width)
            .attr('height', height);
        
        const mapGroup = svg.append('g');
        const tooltip = d3.select('.map-tooltip');
        
        // Setup projection for Montenegro
        const projection = d3.geoMercator()
            .center([19.25, 42.75])
            .scale(12000)
            .translate([width / 2, height / 2]);
        
        const path = d3.geoPath().projection(projection);
        
        // Load and render the map
        d3.json('/montenegro.json').then(function(geojson) {
            // Draw municipalities
            mapGroup.selectAll('.municipality')
                .data(geojson.features)
                .enter()
                .append('path')
                .attr('class', 'municipality')
                .attr('d', path)
                .attr('data-name', d => d.properties.name)
                .on('mouseover', function(event, d) {
                    tooltip
                        .style('opacity', 1)
                        .html(d.properties.name)
                        .style('left', (event.pageX + 10) + 'px')
                        .style('top', (event.pageY - 28) + 'px');
                    
                    d3.select(this).classed('active', true);
                })
                .on('mouseout', function() {
                    tooltip.style('opacity', 0);
                    
                    // Only remove active class if this municipality isn't selected in filter
                    const currentFilter = document.getElementById('municipality').value;
                    const municipalityName = d3.select(this).attr('data-name');
                    if (currentFilter.toLowerCase() !== municipalityName.toLowerCase()) {
                        d3.select(this).classed('active', false);
                    }
                })
                .on('click', function(event, d) {
                    const municipalityName = d.properties.name;
                    
                    if (isLoading) return;
                    
                    // Reset all filter dropdowns to their default "All..." values
                    filterSelects.forEach(select => {
                        select.value = '';
                    });
                    
                    // Directly apply municipality filter via AJAX (bypass dropdown matching)
                    const params = new URLSearchParams();
                    params.set('municipality', municipalityName);
                    
                    // Update browser URL
                    const newUrl = '/public?' + params.toString();
                    window.history.pushState({}, '', newUrl);
                    
                    // Set loading state
                    isLoading = true;
                    
                    // Fetch filtered results directly
                    fetch('/public?' + params.toString())
                        .then(response => response.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            
                            // Update stats cards
                            const statsCards = doc.querySelectorAll('.stats-card');
                            document.querySelectorAll('.stats-card').forEach((card, index) => {
                                if (statsCards[index]) {
                                    card.innerHTML = statsCards[index].innerHTML;
                                }
                            });
                            
                            // Update projects list
                            const newProjectsList = doc.querySelector('.projects-list');
                            document.querySelector('.projects-list').innerHTML = newProjectsList.innerHTML;
                            
                            // Update project count in heading
                            const newHeading = doc.querySelector('.results-section h2');
                            const currentHeading = document.querySelector('.results-section h2');
                            if (newHeading && currentHeading) {
                                currentHeading.textContent = newHeading.textContent;
                            }
                            
                            // Update or remove Load More button
                            const oldLoadMoreContainer = document.querySelector('#loadMoreBtn')?.parentElement;
                            const newLoadMoreBtn = doc.querySelector('#loadMoreBtn');
                            
                            if (newLoadMoreBtn && oldLoadMoreContainer) {
                                const newContainer = newLoadMoreBtn.parentElement;
                                oldLoadMoreContainer.innerHTML = newContainer.innerHTML;
                                attachLoadMoreHandler();
                            } else if (oldLoadMoreContainer) {
                                oldLoadMoreContainer.remove();
                            }
                            
                            // Update filter dropdowns with new options
                            params.set('get_filter_options', '1');
                            fetch('/public?' + params.toString())
                                .then(response => response.json())
                                .then(data => {
                                    updateDropdown('sector', data.sectors, filterSelects);
                                    updateDropdown('municipality', data.municipalities, filterSelects);
                                    updateDropdown('program', data.programs, filterSelects);
                                    updateDropdown('type_of_programme', data.typeOfProgrammes, filterSelects);
                                    updateDropdown('start_year', data.years, filterSelects);
                                    updateDropdown('end_year', data.years, filterSelects);
                                    updateDropdown('beneficiary', data.beneficiaries, filterSelects);
                                    
                                    // Set municipality filter value after dropdown update
                                    const municipalitySelect = document.getElementById('municipality');
                                    const options = municipalitySelect.options;
                                    for (let i = 0; i < options.length; i++) {
                                        if (options[i].value && 
                                            options[i].value.toLowerCase() === municipalityName.toLowerCase()) {
                                            municipalitySelect.value = options[i].value;
                                            break;
                                        }
                                    }
                                })
                                .catch(error => console.error('Error updating filter options:', error));
                            
                            isLoading = false;
                            
                            // Highlight the selected municipality
                            mapGroup.selectAll('.municipality').classed('active', false);
                            d3.select(this).classed('active', true);
                        })
                        .catch(error => {
                            console.error('Error loading filtered results:', error);
                            isLoading = false;
                        });
                });
            
            // Add municipality labels
            mapGroup.selectAll('.municipality-label')
                .data(geojson.features)
                .enter()
                .append('text')
                .attr('class', 'municipality-label')
                .attr('transform', d => {
                    const centroid = path.centroid(d);
                    return `translate(${centroid[0]}, ${centroid[1]})`;
                })
                .text(d => d.properties.name);
            
            // Highlight municipality if filter is active
            const currentFilter = '<?= $filterMunicipality ?>';
            if (currentFilter) {
                mapGroup.selectAll('.municipality')
                    .filter(function(d) {
                        return d.properties.name.toLowerCase() === currentFilter.toLowerCase();
                    })
                    .classed('active', true);
            }
        }).catch(function(error) {
            console.error('Error loading map data:', error);
            mapContainer.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #666;">Map data could not be loaded</div>';
        });
    })();
    </script>
</body>
</html>
