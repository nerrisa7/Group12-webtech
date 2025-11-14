<?php
require_once 'config/auth.php';
requireLogin();

$user = getCurrentUser($conn);
if (!$user) {
    logoutUser();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_report'])) {
    $period_start = mysqli_real_escape_string($conn, $_POST['report_period_start'] ?? '');
    $period_end = mysqli_real_escape_string($conn, $_POST['report_period_end'] ?? '');
    
    if ($period_start && $period_end) {
        $esg_score = calculateESGScore($conn, $user_id, $period_start, $period_end);
        $status = 'complete';
        $generated_date = date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO reports (user_id, period_start, period_end, esg_score, generated_date, status) 
                VALUES ('$user_id', '$period_start', '$period_end', '$esg_score', '$generated_date', '$status')";
        
        if (mysqli_query($conn, $sql)) {
            $success_message = "Report generated successfully!";
        } else {
            $error_message = "Error generating report: " . mysqli_error($conn);
        }
    } else {
        $error_message = "Please select both start and end dates.";
    }
}

function calculateESGScore($conn, $user_id, $period_start, $period_end) {
    $env_query = "SELECT AVG(e.value) as avg_value 
                  FROM esg_data e 
                  JOIN esg_kpis k ON e.kpi_id = k.kpi_id 
                  WHERE e.user_id = '$user_id' AND e.category = 'environmental' 
                  AND e.period_date BETWEEN '$period_start' AND '$period_end'";
    $env_result = mysqli_query($conn, $env_query);
    $env_avg = $env_result && mysqli_num_rows($env_result) > 0 ? mysqli_fetch_assoc($env_result)['avg_value'] : 0;
    
    $social_query = "SELECT AVG(e.value) as avg_value 
                     FROM esg_data e 
                     JOIN esg_kpis k ON e.kpi_id = k.kpi_id 
                     WHERE e.user_id = '$user_id' AND e.category = 'social' 
                     AND e.period_date BETWEEN '$period_start' AND '$period_end'";
    $social_result = mysqli_query($conn, $social_query);
    $social_avg = $social_result && mysqli_num_rows($social_result) > 0 ? mysqli_fetch_assoc($social_result)['avg_value'] : 0;
    
    $gov_query = "SELECT AVG(e.value) as avg_value 
                  FROM esg_data e 
                  JOIN esg_kpis k ON e.kpi_id = k.kpi_id 
                  WHERE e.user_id = '$user_id' AND e.category = 'governance' 
                  AND e.period_date BETWEEN '$period_start' AND '$period_end'";
    $gov_result = mysqli_query($conn, $gov_query);
    $gov_avg = $gov_result && mysqli_num_rows($gov_result) > 0 ? mysqli_fetch_assoc($gov_result)['avg_value'] : 0;
    
    $score = 50 + (($env_avg + $social_avg + $gov_avg) / 3);
    return round(max(0, min(100, $score)), 1);
}

$env_query = "SELECT COUNT(DISTINCT kpi_id) as kpi_count, COUNT(*) as data_count 
              FROM esg_data WHERE user_id = '$user_id' AND category = 'environmental'";
$env_result = mysqli_query($conn, $env_query);
$env_data = $env_result ? mysqli_fetch_assoc($env_result) : ['kpi_count' => 0, 'data_count' => 0];

$social_query = "SELECT COUNT(DISTINCT kpi_id) as kpi_count, COUNT(*) as data_count 
                 FROM esg_data WHERE user_id = '$user_id' AND category = 'social'";
$social_result = mysqli_query($conn, $social_query);
$social_data = $social_result ? mysqli_fetch_assoc($social_result) : ['kpi_count' => 0, 'data_count' => 0];

$gov_query = "SELECT COUNT(DISTINCT kpi_id) as kpi_count, COUNT(*) as data_count 
              FROM esg_data WHERE user_id = '$user_id' AND category = 'governance'";
$gov_result = mysqli_query($conn, $gov_query);
$gov_data = $gov_result ? mysqli_fetch_assoc($gov_result) : ['kpi_count' => 0, 'data_count' => 0];

$reports_query = "SELECT report_id, period_start, period_end, esg_score, generated_date, status 
                  FROM reports WHERE user_id = '$user_id' ORDER BY generated_date DESC LIMIT 20";
$reports_result = mysqli_query($conn, $reports_query);

function formatDate($date) {
    return date('M j, Y', strtotime($date));
}

function formatDateTime($date) {
    return date('M j, Y H:i', strtotime($date));
}

function getScoreBadge($score) {
    if ($score >= 70) return '<span class="score-badge good">Good</span>';
    if ($score >= 50) return '<span class="score-badge average">Average</span>';
    return '<span class="score-badge poor">Poor</span>';
}

function getStatusBadge($status) {
    return $status === 'complete' 
        ? '<span class="status-complete">Complete</span>'
        : '<span class="status-pending">Pending</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Cenviro</title>
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
                    <li><a href="dashboard.php" class="nav-btn"><span class="material-symbols-outlined">dashboard</span> Dashboard</a></li>
                    <li><a href="data_input.php" class="nav-btn"><span class="material-symbols-outlined">edit_note</span> Data Input</a></li>
                    <li><a href="reports.php" class="nav-btn active"><span class="material-symbols-outlined">description</span> Reports</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <div class="main-title">
                <h2>ESG Reports</h2>
            </div>

            <?php if ($success_message): ?>
            <div class="alert success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
            <div class="alert error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <div class="content-section">
                <h3 class="section-title">Your ESG Data Summary</h3>
                <div class="stats-cards">
                    <div class="stat-card">
                        <div class="card-header">
                            <span class="material-symbols-outlined card-icon environmental">eco</span>
                            <div class="card-title">Environmental Data</div>
                        </div>
                        <div class="card-value"><?php echo $env_data['data_count']; ?></div>
                        <div class="card-unit"><?php echo $env_data['kpi_count']; ?> KPIs tracked</div>
                    </div>

                    <div class="stat-card">
                        <div class="card-header">
                            <span class="material-symbols-outlined card-icon social">groups</span>
                            <div class="card-title">Social Data</div>
                        </div>
                        <div class="card-value"><?php echo $social_data['data_count']; ?></div>
                        <div class="card-unit"><?php echo $social_data['kpi_count']; ?> KPIs tracked</div>
                    </div>

                    <div class="stat-card">
                        <div class="card-header">
                            <span class="material-symbols-outlined card-icon governance">gavel</span>
                            <div class="card-title">Governance Data</div>
                        </div>
                        <div class="card-value"><?php echo $gov_data['data_count']; ?></div>
                        <div class="card-unit"><?php echo $gov_data['kpi_count']; ?> KPIs tracked</div>
                    </div>
                </div>
            </div>

            <div class="content-section">
                <h3 class="section-title">Generate New Report</h3>
                <form method="POST" action="reports.php" class="report-form">
                    <div class="form-grid form-grid-2col">
                        <div class="form-group">
                            <label for="report_period_start">Report Period Start</label>
                            <input type="date" id="report_period_start" name="report_period_start" required>
                        </div>
                        <div class="form-group">
                            <label for="report_period_end">Report Period End</label>
                            <input type="date" id="report_period_end" name="report_period_end" required>
                        </div>
                    </div>
                    <button type="submit" name="generate_report" class="btn-primary">
                        <span class="material-symbols-outlined btn-with-icon">add_circle</span>
                        Generate Report
                    </button>
                </form>
            </div>

            <div class="content-section">
                <h3 class="section-title">Generated Reports</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Report ID</th>
                            <th>Period</th>
                            <th>ESG Score</th>
                            <th>Generated Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($reports_result && mysqli_num_rows($reports_result) > 0): ?>
                            <?php while ($report = mysqli_fetch_assoc($reports_result)): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($report['report_id']); ?></td>
                                <td><?php echo formatDate($report['period_start']) . ' - ' . formatDate($report['period_end']); ?></td>
                                <td><strong class="score-value"><?php echo htmlspecialchars($report['esg_score']); ?></strong> <?php echo getScoreBadge($report['esg_score']); ?></td>
                                <td><?php echo formatDateTime($report['generated_date']); ?></td>
                                <td><?php echo getStatusBadge($report['status']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align: center;">No reports generated yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="content-section">
                <h3 class="section-title">UN Sustainable Development Goals (SDG) Mapping</h3>
                <p class="sdg-intro">Your ESG KPIs contribute to the following UN SDGs:</p>
                
                <div class="sdg-grid">
                    <div class="sdg-card sdg-card-7">
                        <strong class="sdg-name">SDG 7: Affordable & Clean Energy</strong>
                        <p class="sdg-title">Your energy consumption and emissions tracking</p>
                    </div>
                    
                    <div class="sdg-card sdg-card-13">
                        <strong class="sdg-name">SDG 13: Climate Action</strong>
                        <p class="sdg-title">Your carbon emissions monitoring</p>
                    </div>
                    
                    <div class="sdg-card sdg-card-5">
                        <strong class="sdg-name">SDG 5: Gender Equality</strong>
                        <p class="sdg-title">Your diversity and inclusion metrics</p>
                    </div>
                    
                    <div class="sdg-card sdg-card-8">
                        <strong class="sdg-name">SDG 8: Decent Work</strong>
                        <p class="sdg-title">Your employee training and safety tracking</p>
                    </div>
                    
                    <div class="sdg-card sdg-card-16">
                        <strong class="sdg-name">SDG 16: Peace & Justice</strong>
                        <p class="sdg-title">Your governance and ethics policies</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
<?php mysqli_close($conn); ?>
