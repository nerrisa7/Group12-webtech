<?php
require_once 'config/auth.php';
requireLogin();

$user = getCurrentUser($conn);
if (!$user) {
    logoutUser();
}

$user_id = $_SESSION['user_id'];

$esg_query = "SELECT esg_score FROM reports WHERE user_id = '$user_id' ORDER BY generated_date DESC LIMIT 1";
$esg_result = mysqli_query($conn, $esg_query);
$esg_score = 0;
if ($esg_result && mysqli_num_rows($esg_result) > 0) {
    $row = mysqli_fetch_assoc($esg_result);
    $esg_score = $row['esg_score'] ?? 0;
}

$emissions_query = "SELECT 
    SUM(CASE WHEN k.kpi_name LIKE '%Scope 1%' OR k.kpi_name LIKE '%Emissions%' THEN e.value ELSE 0 END) as total_emissions
    FROM esg_data e
    JOIN esg_kpis k ON e.kpi_id = k.kpi_id
    WHERE e.user_id = '$user_id' AND e.category = 'environmental'
    AND (k.kpi_name LIKE '%Scope%' OR k.kpi_name LIKE '%Emissions%')";
$emissions_result = mysqli_query($conn, $emissions_query);
$total_emissions = 0;
if ($emissions_result && mysqli_num_rows($emissions_result) > 0) {
    $row = mysqli_fetch_assoc($emissions_result);
    $total_emissions = round($row['total_emissions'] ?? 0, 1);
}

$energy_query = "SELECT e.value FROM esg_data e 
                 JOIN esg_kpis k ON e.kpi_id = k.kpi_id 
                 WHERE e.user_id = '$user_id' AND k.kpi_name LIKE '%Energy%' 
                 ORDER BY e.created_at DESC LIMIT 1";
$energy_result = mysqli_query($conn, $energy_query);
$energy_usage = 12500;
if ($energy_result && mysqli_num_rows($energy_result) > 0) {
    $row = mysqli_fetch_assoc($energy_result);
    $energy_usage = $row['value'] ?? 12500;
}

$water_query = "SELECT e.value FROM esg_data e 
                JOIN esg_kpis k ON e.kpi_id = k.kpi_id 
                WHERE e.user_id = '$user_id' AND k.kpi_name LIKE '%Water%' 
                ORDER BY e.created_at DESC LIMIT 1";
$water_result = mysqli_query($conn, $water_query);
$water_usage = 450;
if ($water_result && mysqli_num_rows($water_result) > 0) {
    $row = mysqli_fetch_assoc($water_result);
    $water_usage = round($row['value'] ?? 450, 0);
}

$employees_query = "SELECT e.value FROM esg_data e 
                    JOIN esg_kpis k ON e.kpi_id = k.kpi_id 
                    WHERE e.user_id = '$user_id' AND k.kpi_name LIKE '%Employees%' 
                    ORDER BY e.created_at DESC LIMIT 1";
$employees_result = mysqli_query($conn, $employees_query);
$total_employees = 45;
if ($employees_result && mysqli_num_rows($employees_result) > 0) {
    $row = mysqli_fetch_assoc($employees_result);
    $total_employees = round($row['value'] ?? 45, 0);
}

$diversity_query = "SELECT e.value FROM esg_data e 
                    JOIN esg_kpis k ON e.kpi_id = k.kpi_id 
                    WHERE e.user_id = '$user_id' AND k.kpi_name LIKE '%Female%' 
                    ORDER BY e.created_at DESC LIMIT 1";
$diversity_result = mysqli_query($conn, $diversity_query);
$gender_diversity = 42.5;
if ($diversity_result && mysqli_num_rows($diversity_result) > 0) {
    $row = mysqli_fetch_assoc($diversity_result);
    $gender_diversity = round($row['value'] ?? 42.5, 1);
}

$gov_query = "SELECT COUNT(*) as count FROM esg_data e 
              JOIN esg_kpis k ON e.kpi_id = k.kpi_id 
              WHERE e.user_id = '$user_id' AND e.category = 'governance' 
              AND e.value > 0";
$gov_result = mysqli_query($conn, $gov_query);
$gov_compliant = 2;
if ($gov_result && mysqli_num_rows($gov_result) > 0) {
    $row = mysqli_fetch_assoc($gov_result);
    $gov_compliant = min($row['count'] ?? 2, 2);
}

$recycle_query = "SELECT e.value FROM esg_data e 
                  JOIN esg_kpis k ON e.kpi_id = k.kpi_id 
                  WHERE e.user_id = '$user_id' AND k.kpi_name LIKE '%Recycling%' 
                  ORDER BY e.created_at DESC LIMIT 1";
$recycle_result = mysqli_query($conn, $recycle_query);
$recycling_rate = 75.0;
if ($recycle_result && mysqli_num_rows($recycle_result) > 0) {
    $row = mysqli_fetch_assoc($recycle_result);
    $recycling_rate = round($row['value'] ?? 75.0, 1);
}

$trend_query = "SELECT DATE_FORMAT(period_date, '%Y-%m') as month, SUM(value) as emissions
                FROM esg_data e
                JOIN esg_kpis k ON e.kpi_id = k.kpi_id
                WHERE e.user_id = '$user_id' AND k.kpi_name LIKE '%Emissions%'
                AND period_date >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
                GROUP BY DATE_FORMAT(period_date, '%Y-%m')
                ORDER BY month DESC LIMIT 3";
$trend_result = mysqli_query($conn, $trend_query);
$trends = [];
while ($row = mysqli_fetch_assoc($trend_result)) {
    $trends[] = $row;
}

$firstName = explode(' ', $user['name'])[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Cenviro</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
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

            <?php if (!empty($trends)): ?>
            <div class="content-section">
                <h3 class="section-title">Emissions Trend</h3>
                <div class="emissions-chart">
                    <?php 
                    $months = ['Jan 2025', 'Dec 2024', 'Nov 2024'];
                    $max_emissions = max(array_column($trends, 'emissions')) ?: 100;
                    $i = 0;
                    foreach (array_reverse($trends) as $trend): 
                        $height = ($trend['emissions'] / $max_emissions) * 100;
                    ?>
                    <div class="chart-bar">
                        <div class="chart-bar-fill" style="height: <?php echo $height; ?>%"></div>
                        <div class="chart-value"><?php echo number_format($trend['emissions'], 1); ?> tCO₂e</div>
                        <div class="chart-label"><?php echo $months[$i++] ?? date('M Y', strtotime($trend['month'])); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="content-section">
                <h3 class="section-title">Quick Actions</h3>
                <div class="quick-actions">
                    <a href="data_input.php" class="btn-primary btn-link">Add New Data</a>
                    <a href="reports.php" class="btn-secondary btn-link">View Reports</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
<?php mysqli_close($conn); ?>

