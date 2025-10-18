<?php
require_once __DIR__ . '/../config/init.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDbConnection();

// Get overall statistics
$statsQuery = "SELECT 
    COUNT(*) as total_projects,
    SUM(eu_contribution_mne) as total_funding,
    COUNT(CASE WHEN end_date IS NULL OR end_date >= CURRENT_DATE THEN 1 END) as ongoing_count,
    COUNT(CASE WHEN end_date < CURRENT_DATE THEN 1 END) as completed_count
FROM projects";
$statsStmt = $pdo->query($statsQuery);
$stats = $statsStmt->fetch();

$totalProjects = $stats['total_projects'] ?? 0;
$totalFunding = $stats['total_funding'] ?? 0;
$ongoingCount = $stats['ongoing_count'] ?? 0;
$completedCount = $stats['completed_count'] ?? 0;

// Get projects by sector
$sectorQuery = "SELECT sector, COUNT(*) as count FROM (
    SELECT sector_1 as sector FROM projects WHERE sector_1 IS NOT NULL AND TRIM(sector_1) != '' 
    UNION ALL 
    SELECT sector_2 as sector FROM projects WHERE sector_2 IS NOT NULL AND TRIM(sector_2) != ''
) s
GROUP BY sector
ORDER BY count DESC
LIMIT 10";
$sectorStmt = $pdo->query($sectorQuery);
$sectorStats = $sectorStmt->fetchAll();

// Get projects by municipality
$municipalityQuery = "SELECT municipality, COUNT(*) as count 
FROM projects 
WHERE municipality IS NOT NULL AND TRIM(municipality) != '' 
GROUP BY municipality 
ORDER BY count DESC 
LIMIT 10";
$municipalityStmt = $pdo->query($municipalityQuery);
$municipalityStats = $municipalityStmt->fetchAll();

// Get projects by program
$programQuery = "SELECT programme, COUNT(*) as count 
FROM projects 
WHERE programme IS NOT NULL AND TRIM(programme) != '' 
GROUP BY programme 
ORDER BY count DESC 
LIMIT 10";
$programStmt = $pdo->query($programQuery);
$programStats = $programStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - EU Projects in MNE</title>
    <link rel="stylesheet" href="/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container">
        <div class="main-content">
            <h1>Dashboard</h1>
            <p style="margin-bottom: 2rem;">Welcome, <?= htmlspecialchars($user['full_name']) ?>!</p>
            
            <!-- Overall Statistics -->
            <section class="statistics-section">
                <h2 style="color: #003399; margin-bottom: 1.5rem;">Overall Statistics</h2>
                <div class="dashboard-stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">📊</div>
                        <h3>Total Projects</h3>
                        <p class="stat-value"><?= number_format($totalProjects) ?></p>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">💰</div>
                        <h3>Total EU Funding</h3>
                        <p class="stat-value">€<?= number_format($totalFunding, 2) ?></p>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">🟢</div>
                        <h3>Ongoing Projects</h3>
                        <p class="stat-value"><?= number_format($ongoingCount) ?></p>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">✅</div>
                        <h3>Completed Projects</h3>
                        <p class="stat-value"><?= number_format($completedCount) ?></p>
                    </div>
                </div>
            </section>
            
            <!-- Charts Section -->
            <section class="charts-section">
                <h2 style="color: #003399; margin-bottom: 1.5rem; margin-top: 3rem;">Project Distribution</h2>
                <div class="charts-grid">
                    <!-- Projects by Sector Chart -->
                    <div class="chart-container">
                        <h3>Top 10 Sectors</h3>
                        <canvas id="sectorChart"></canvas>
                    </div>
                    
                    <!-- Projects by Municipality Chart -->
                    <div class="chart-container">
                        <h3>Top 10 Municipalities</h3>
                        <canvas id="municipalityChart"></canvas>
                    </div>
                    
                    <!-- Projects by Program Chart -->
                    <div class="chart-container">
                        <h3>Top 10 Programs</h3>
                        <canvas id="programChart"></canvas>
                    </div>
                    
                    <!-- Status Distribution Chart -->
                    <div class="chart-container">
                        <h3>Status Distribution</h3>
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </section>
            
            <div class="quick-actions">
                <h2>Quick Actions</h2>
                <a href="/profile.php" class="btn btn-primary">Edit My Profile</a>
                <a href="/projects.php" class="btn btn-secondary">View All Projects</a>
                <?php if (isAdmin()): ?>
                    <a href="/users.php" class="btn btn-secondary">Manage Users</a>
                    <a href="/projects-import.php" class="btn btn-secondary">Import Projects</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Chart.js configuration
        const chartColors = {
            primary: '#003399',
            secondary: '#FFCC00',
            success: '#4CAF50',
            info: '#2196F3',
            warning: '#FF9800',
            danger: '#F44336',
            purple: '#9C27B0',
            teal: '#009688',
            pink: '#E91E63',
            indigo: '#3F51B5'
        };
        
        const defaultChartOptions = {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            size: 11
                        }
                    }
                }
            }
        };
        
        // Sector Chart
        <?php if (!empty($sectorStats)): ?>
        const sectorCtx = document.getElementById('sectorChart');
        new Chart(sectorCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($sectorStats, 'sector')) ?>,
                datasets: [{
                    label: 'Number of Projects',
                    data: <?= json_encode(array_column($sectorStats, 'count')) ?>,
                    backgroundColor: chartColors.primary,
                    borderColor: chartColors.primary,
                    borderWidth: 1
                }]
            },
            options: {
                ...defaultChartOptions,
                indexAxis: 'y',
                plugins: {
                    ...defaultChartOptions.plugins,
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
        <?php endif; ?>
        
        // Municipality Chart
        <?php if (!empty($municipalityStats)): ?>
        const municipalityCtx = document.getElementById('municipalityChart');
        new Chart(municipalityCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_column($municipalityStats, 'municipality')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($municipalityStats, 'count')) ?>,
                    backgroundColor: [
                        chartColors.primary,
                        chartColors.secondary,
                        chartColors.success,
                        chartColors.info,
                        chartColors.warning,
                        chartColors.danger,
                        chartColors.purple,
                        chartColors.teal,
                        chartColors.pink,
                        chartColors.indigo
                    ]
                }]
            },
            options: defaultChartOptions
        });
        <?php endif; ?>
        
        // Program Chart
        <?php if (!empty($programStats)): ?>
        const programCtx = document.getElementById('programChart');
        new Chart(programCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($programStats, 'programme')) ?>,
                datasets: [{
                    label: 'Number of Projects',
                    data: <?= json_encode(array_column($programStats, 'count')) ?>,
                    backgroundColor: chartColors.secondary,
                    borderColor: chartColors.secondary,
                    borderWidth: 1
                }]
            },
            options: {
                ...defaultChartOptions,
                indexAxis: 'y',
                plugins: {
                    ...defaultChartOptions.plugins,
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
        <?php endif; ?>
        
        // Status Chart
        const statusCtx = document.getElementById('statusChart');
        new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: ['Ongoing', 'Completed'],
                datasets: [{
                    data: [<?= $ongoingCount ?>, <?= $completedCount ?>],
                    backgroundColor: [
                        chartColors.success,
                        '#9E9E9E'
                    ]
                }]
            },
            options: defaultChartOptions
        });
    </script>
</body>
</html>
