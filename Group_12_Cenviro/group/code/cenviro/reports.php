<?php
/*This page handles ESG report generation and display.*/
require_once 'config/auth.php';
$auth->requireLogin();

/*Get current user*/
$user = $auth->getCurrentUser();
if (!$user) {
    $auth->logout();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';


// Handle report generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_report'])) {
    $period_start = trim($_POST['report_period_start'] ?? '');
    $period_end = trim($_POST['report_period_end'] ?? '');
    
    if ($period_start && $period_end) {
        // Use Report class to generate the report
        if ($report->generateReport($user_id, $period_start, $period_end)) {
            $success_message = "Report generated successfully!";
        } else {
            $error_message = "Error generating report. Please try again.";
        }
    } else {
        $error_message = "Please select both start and end dates.";
    }
}

// Get data summaries using ESGData class
$env_data = $esgData->getDataSummary($user_id, 'environmental');
$social_data = $esgData->getDataSummary($user_id, 'social');
$gov_data = $esgData->getDataSummary($user_id, 'governance');

// Get user reports using Report class
$reports = $report->getUserReports($user_id, 20);

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
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
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
                    <li><a href="company_settings.php" class="nav-btn"><span class="material-symbols-outlined">settings</span> Company Settings</a></li>
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
                <h3 class="section-title">ESG Benchmark Reference</h3>
                <p>Download the complete Ghana ESG KPI Report to view all measurement methodologies and ideal benchmarks:</p>
                <a href="Ghana_ESG_KPI_Report.pdf" download class="btn-primary" style="display: inline-block; margin-top: 15px;">
                    <span class="material-symbols-outlined btn-with-icon">download</span>
                    Download ESG KPI Report (PDF)
                </a>
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
                <div style="margin-bottom: 15px;">
                    <input type="text" id="reportSearch" placeholder="Search reports by date or score..." style="width: 100%; max-width: 400px; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                <table class="data-table" id="reportsTable">
                    <thead>
                        <tr>
                            <th>Report ID</th>
                            <th>Period</th>
                            <th>ESG Score</th>
                            <th>Generated Date</th>
                            <th>Status</th>
                            <th>Download</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($reports)): ?>
                            <?php foreach ($reports as $report_item): ?>
                            <tr class="report-row">
                                <td>#<?php echo htmlspecialchars($report_item['report_id']); ?></td>
                                <td><?php echo formatDate($report_item['period_start']) . ' - ' . formatDate($report_item['period_end']); ?></td>
                                <td><strong class="score-value"><?php echo htmlspecialchars($report_item['esg_score']); ?></strong> <?php echo getScoreBadge($report_item['esg_score']); ?></td>
                                <td><?php echo formatDateTime($report_item['generated_date']); ?></td>
                                <td><?php echo getStatusBadge($report_item['status']); ?></td>
                                <td>
                                    <a href="download_report.php?id=<?php echo $report_item['report_id']; ?>&format=csv" 
                                       class="btn-primary" 
                                       style="padding: 8px 15px; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; border-radius: 4px;">
                                        <span class="material-symbols-outlined" style="font-size: 18px;">download</span>
                                        Download CSV
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align: center;">No reports generated yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="content-section">
                <h3 class="section-title">ESG Metrics Reference</h3>
                <p class="section-intro">These are the key ESG metrics used to calculate your ESG score. Each metric is compared against industry benchmarks.</p>
                    <table class="data-table benchmark-table">
                        <thead>
                            <tr>
                            <th>Metric</th>
                            <th>Ideal Benchmark</th>
                            <th>Direction</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php 
                        $metrics = $report->getEsgMetrics();
                        foreach ($metrics as $metric): 
                        ?>
                                <tr>
                                <td><?php echo htmlspecialchars($metric['name']); ?></td>
                                <td><?php echo htmlspecialchars($metric['ideal']); ?></td>
                                <td><?php echo $metric['direction'] === 'lower_better' ? 'Lower is Better' : 'Higher is Better'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
            </div>

            <div class="content-section">
                <h3 class="section-title">UN Sustainable Development Goals (SDG) Mapping</h3>
                <p class="sdg-intro">Your ESG KPIs contribute to the following UN SDGs:</p>
                
                <div class="sdg-grid">
                    <a href="https://www.un.org/sustainabledevelopment/energy/" target="_blank" class="sdg-card sdg-card-7">
                        <strong class="sdg-name">SDG 7: Affordable & Clean Energy</strong>
                        <p class="sdg-title">Your energy consumption and emissions tracking</p>
                    </a>
                    
                    <a href="https://www.un.org/sustainabledevelopment/climate-change/" target="_blank" class="sdg-card sdg-card-13">
                        <strong class="sdg-name">SDG 13: Climate Action</strong>
                        <p class="sdg-title">Your carbon emissions monitoring</p>
                    </a>
                    
                    <a href="https://www.un.org/sustainabledevelopment/gender-equality/" target="_blank" class="sdg-card sdg-card-5">
                        <strong class="sdg-name">SDG 5: Gender Equality</strong>
                        <p class="sdg-title">Your diversity and inclusion metrics</p>
                    </a>
                    
                    <a href="https://www.un.org/sustainabledevelopment/economic-growth/" target="_blank" class="sdg-card sdg-card-8">
                        <strong class="sdg-name">SDG 8: Decent Work</strong>
                        <p class="sdg-title">Your employee training and safety tracking</p>
                    </a>
                    
                    <a href="https://www.un.org/sustainabledevelopment/peace-justice/" target="_blank" class="sdg-card sdg-card-16">
                        <strong class="sdg-name">SDG 16: Peace & Justice</strong>
                        <p class="sdg-title">Your governance and ethics policies</p>
                    </a>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('reportSearch');
            const reportRows = document.querySelectorAll('.report-row');

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase().trim();
                    let visibleCount = 0;

                    reportRows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        if (text.includes(searchTerm)) {
                            row.style.display = '';
                            visibleCount++;
                        } else {
                            row.style.display = 'none';
                        }
                    });

                    const tbody = document.querySelector('#reportsTable tbody');
                    const noResultsRow = tbody.querySelector('.no-results');
                    if (visibleCount === 0 && searchTerm !== '') {
                        if (!noResultsRow) {
                            const row = document.createElement('tr');
                            row.className = 'no-results';
                            row.innerHTML = '<td colspan="6" style="text-align: center;">No reports found matching your search.</td>';
                            tbody.appendChild(row);
                        }
                    } else {
                        if (noResultsRow) {
                            noResultsRow.remove();
                        }
                    }
                });
            }

            const reportForm = document.querySelector('.report-form');
            if (reportForm) {
                reportForm.addEventListener('submit', function(e) {
                    const startDate = document.getElementById('report_period_start').value;
                    const endDate = document.getElementById('report_period_end').value;

                    if (!startDate || !endDate) {
                        e.preventDefault();
                        alert('Please select both start and end dates.');
                        return false;
                    }

                    if (new Date(startDate) > new Date(endDate)) {
                        e.preventDefault();
                        alert('Start date must be before end date.');
                        return false;
                    }
                });
            }
        });
    </script>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> Cenviro. All rights reserved. | Generated using Cenviro - ESG KPI Tracking System</p>
    </footer>
</body>
</html>
<?php $database->close(); ?>
