<?php
/*This page displays the main dashboard with ESG statistics.*/
require_once 'config/auth.php';
$auth->requireLogin();

// Get current user
$user = $auth->getCurrentUser();
if (!$user) {
    $auth->logout();
}

$user_id = $_SESSION['user_id'];

// Get all dashboard statistics using the Dashboard class
$stats = $dashboard->getDashboardStats($user_id);

// Get trend data for charts
$trend_data = $dashboard->getTrendData($user_id);

// Extract individual stats for easier use in the template
$esg_score = $stats['esg_score'];
$total_emissions = $stats['total_emissions'];
$energy_usage = $stats['energy_usage'];
$water_usage = $stats['water_usage'];
$total_employees = $stats['total_employees'];
$gender_diversity = $stats['gender_diversity'];
$gov_compliant = $stats['gov_compliant'];
$recycling_rate = $stats['recycling_rate'];

$firstName = explode(' ', $user['name'])[0];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Cenviro</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Chart.js library for displaying trends -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <header class="header">
            <div class="header-left">
                <h2><?php echo htmlspecialchars($user['org_name'] ?? 'Organization'); ?></h2>
            </div>
            <div class="header-right">
                <div class="user-info">
                    <span class="material-symbols-outlined">person</span>
                    <span><?php echo htmlspecialchars($user['name']); ?></span>
                </div>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </header>

        <aside class="sidebar">
            <div class="sidebar-logo">
                <img src="logo.png" alt="Cenviro Logo" class="logo-img">
                <h2><a href="dashboard.php">Cenviro</a></h2>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php" class="nav-btn active"><span class="material-symbols-outlined">dashboard</span> Dashboard</a></li>
                    <li><a href="data_input.php" class="nav-btn"><span class="material-symbols-outlined">edit_note</span> Data Input</a></li>
                    <li><a href="reports.php" class="nav-btn"><span class="material-symbols-outlined">description</span> Reports</a></li>
                    <li><a href="company_settings.php" class="nav-btn"><span class="material-symbols-outlined">settings</span> Company Settings</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <div class="main-title">
                <h2><span class="welcome">Welcome</span>, <?php echo htmlspecialchars($firstName); ?>!</h2>
            </div>

            <div class="stats-cards">
                <div class="stat-card">
                    <div class="card-header">
                        <span class="material-symbols-outlined card-icon score">eco</span>
                        <div class="card-title">ESG Score</div>
                    </div>
                    <div class="card-value"><?php echo $esg_score > 0 ? number_format($esg_score, 1) : 'N/A'; ?></div>
                    <?php if ($esg_score > 0): ?>
                    <span class="score-badge <?php echo $esg_score >= 70 ? 'good' : ($esg_score >= 50 ? 'average' : 'poor'); ?>">
                        <?php echo $esg_score >= 70 ? 'Good' : ($esg_score >= 50 ? 'Average' : 'Poor'); ?>
                    </span>
                    <?php endif; ?>
                </div>

                <div class="stat-card">
                    <div class="card-header">
                        <span class="material-symbols-outlined card-icon environmental">co2</span>
                        <div class="card-title">Total Emissions</div>
                    </div>
                    <div class="card-value"><?php echo number_format($total_emissions, 1); ?></div>
                    <div class="card-unit">tCO₂e</div>
                </div>

                <div class="stat-card">
                    <div class="card-header">
                        <span class="material-symbols-outlined card-icon environmental">bolt</span>
                        <div class="card-title">Energy Usage</div>
                    </div>
                    <div class="card-value"><?php echo number_format($energy_usage, 0); ?></div>
                    <div class="card-unit">kWh</div>
                </div>

                <div class="stat-card">
                    <div class="card-header">
                        <span class="material-symbols-outlined card-icon environmental">water_drop</span>
                        <div class="card-title">Water Usage</div>
                    </div>
                    <div class="card-value"><?php echo number_format($water_usage, 0); ?></div>
                    <div class="card-unit">m³</div>
                </div>

                <div class="stat-card">
                    <div class="card-header">
                        <span class="material-symbols-outlined card-icon social">groups</span>
                        <div class="card-title">Total Employees</div>
                    </div>
                    <div class="card-value"><?php echo $total_employees; ?></div>
                </div>

                <div class="stat-card">
                    <div class="card-header">
                        <span class="material-symbols-outlined card-icon social">diversity_3</span>
                        <div class="card-title">Gender Diversity</div>
                    </div>
                    <div class="card-value"><?php echo number_format($gender_diversity, 1); ?>%</div>
                    <div class="card-unit">Female</div>
                </div>

                <div class="stat-card">
                    <div class="card-header">
                        <span class="material-symbols-outlined card-icon governance">gavel</span>
                        <div class="card-title">Governance</div>
                    </div>
                    <div class="card-value"><?php echo $gov_compliant; ?>/2</div>
                    <div class="card-unit">Compliant</div>
                </div>

                <div class="stat-card">
                    <div class="card-header">
                        <span class="material-symbols-outlined card-icon environmental">recycling</span>
                        <div class="card-title">Recycling Rate</div>
                    </div>
                    <div class="card-value"><?php echo number_format($recycling_rate, 1); ?>%</div>
                </div>
            </div>

            <div class="content-section">
                <h3 class="section-title">Trends (Last 6 Months)</h3>
                <div class="charts-grid">
                    <div class="chart-card">
                        <h4>Environmental Trend</h4>
                        <canvas id="environmentalChart"></canvas>
                    </div>
                    <div class="chart-card">
                        <h4>Social Trend</h4>
                        <canvas id="socialChart"></canvas>
                    </div>
                    <div class="chart-card">
                        <h4>Governance Trend</h4>
                        <canvas id="governanceChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="content-section">
                <h3 class="section-title">Quick Actions</h3>
                <div class="quick-actions">
                    <a href="data_input.php" class="btn-primary btn-link">Add New Data</a>
                    <a href="reports.php" class="btn-secondary btn-link">View Reports</a>
                </div>
            </div>
        </main>
    </div>

    <script>
    // Chart.js configuration - this creates the trend charts
    // We're using Chart.js library (loaded from CDN above)
    
    // Environmental Chart
    const envCtx = document.getElementById('environmentalChart');
    if (envCtx) {
        new Chart(envCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($trend_data['environmental']['labels']); ?>,
                datasets: [{
                    label: 'Environmental',
                    data: <?php echo json_encode($trend_data['environmental']['data']); ?>,
                    borderColor: 'rgb(45, 106, 79)',
                    backgroundColor: 'rgba(45, 106, 79, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Social Chart
    const socialCtx = document.getElementById('socialChart');
    if (socialCtx) {
        new Chart(socialCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($trend_data['social']['labels']); ?>,
                datasets: [{
                    label: 'Social',
                    data: <?php echo json_encode($trend_data['social']['data']); ?>,
                    borderColor: 'rgb(220, 53, 69)',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Governance Chart
    const govCtx = document.getElementById('governanceChart');
    if (govCtx) {
        new Chart(govCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($trend_data['governance']['labels']); ?>,
                datasets: [{
                    label: 'Governance',
                    data: <?php echo json_encode($trend_data['governance']['data']); ?>,
                    borderColor: 'rgb(255, 193, 7)',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    </script>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> Cenviro. All rights reserved. | Generated using Cenviro - ESG KPI Tracking System</p>
    </footer>
</body>
</html>
<?php $database->close(); ?>

