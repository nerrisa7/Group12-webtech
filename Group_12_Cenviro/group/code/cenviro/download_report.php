<?php
/*This file generates and downloads a report as CSV.*/
require_once 'config/auth.php';
$auth->requireLogin();

$user = $auth->getCurrentUser();
if (!$user) {
    $auth->logout();
}

$user_id = $_SESSION['user_id'];
$report_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($report_id <= 0) {
    die("Invalid report ID");
}

// Get report details
$conn = $database->getConnection();
$query = "SELECT * FROM reports WHERE report_id = '$report_id' AND user_id = '$user_id'";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Report not found");
}

$report_data = mysqli_fetch_assoc($result);

// Get ESG metrics
$metrics = $report->getEsgMetrics();
$period_start = $report_data['period_start'];
$period_end = $report_data['period_end'];

// Generate CSV file
$filename = "ESG_Report_" . date('Y-m-d', strtotime($period_start)) . "_to_" . date('Y-m-d', strtotime($period_end)) . ".csv";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// Get company settings
$settings = $companySettings->getSettings();

// Write header information
fputcsv($output, [$settings['company_name'] ?? 'ESG Report']);
if ($settings['ceo']) {
    fputcsv($output, ['CEO: ' . $settings['ceo']]);
}
fputcsv($output, []); // Empty row

// Company information
if ($settings['company_description']) {
    fputcsv($output, ['Company Description:', $settings['company_description']]);
}
if ($settings['full_address']) {
    fputcsv($output, ['Address:', $settings['full_address']]);
}
if ($settings['city'] || $settings['country']) {
    $location = trim(($settings['city'] ?? '') . ', ' . ($settings['country'] ?? ''));
    if ($location !== ',') {
        fputcsv($output, ['Location:', $location]);
    }
}
if ($settings['phone_number']) {
    fputcsv($output, ['Phone:', $settings['phone_number']]);
}
if ($settings['main_email']) {
    fputcsv($output, ['Email:', $settings['main_email']]);
}
if ($settings['website_url']) {
    fputcsv($output, ['Website:', $settings['website_url']]);
}
fputcsv($output, []); // Empty row

// Report details
fputcsv($output, ['Report Period:', date('M j, Y', strtotime($period_start)) . ' - ' . date('M j, Y', strtotime($period_end))]);
fputcsv($output, ['Generated:', date('M j, Y H:i', strtotime($report_data['generated_date']))]);
fputcsv($output, ['ESG Score:', $report_data['esg_score']]);
fputcsv($output, []); // Empty row

// Write metrics table
fputcsv($output, ['ESG Metrics']);
fputcsv($output, ['Metric', 'Ideal Benchmark', 'Direction']);

foreach ($metrics as $metric) {
    $direction = $metric['direction'] === 'lower_better' ? 'Lower is Better' : 'Higher is Better';
    fputcsv($output, [
        $metric['name'],
        $metric['ideal'],
        $direction
    ]);
}

fputcsv($output, []); // Empty row

// Get data entries organized by category
$categories = ['environmental', 'social', 'governance'];
$category_names = [
    'environmental' => 'Environmental',
    'social' => 'Social',
    'governance' => 'Governance'
];

// Write data entries organized by category
fputcsv($output, ['Data Entries Used in Report']);
fputcsv($output, []); // Empty row

foreach ($categories as $category) {
    // Get data for this category
    $category_query = "SELECT e.*, k.kpi_name, k.unit 
                      FROM esg_data e 
                      JOIN esg_kpis k ON e.kpi_id = k.kpi_id 
                      WHERE e.user_id = '$user_id' 
                      AND e.category = '$category'
                      AND e.period_date BETWEEN '$period_start' AND '$period_end'
                      ORDER BY k.kpi_name, e.period_date";
    $category_result = mysqli_query($conn, $category_query);
    
    // Write category header
    fputcsv($output, [$category_names[$category] . ' Data']);
    fputcsv($output, ['KPI Name', 'Value', 'Unit', 'Period Date']);
    
    if ($category_result && mysqli_num_rows($category_result) > 0) {
        while ($data_row = mysqli_fetch_assoc($category_result)) {
            fputcsv($output, [
                $data_row['kpi_name'],
                $data_row['value'],
                $data_row['unit'] ?? '',
                date('M j, Y', strtotime($data_row['period_date']))
            ]);
        }
    } else {
        fputcsv($output, ['No data entries found for this category']);
    }
    
    fputcsv($output, []); // Empty row between categories
}

fputcsv($output, []); // Empty row

// Write data summary
fputcsv($output, ['Data Summary']);
$env_summary = $esgData->getDataSummary($user_id, 'environmental');
$social_summary = $esgData->getDataSummary($user_id, 'social');
$gov_summary = $esgData->getDataSummary($user_id, 'governance');

fputcsv($output, ['Category', 'KPIs Tracked', 'Data Points']);
fputcsv($output, ['Environmental', $env_summary['kpi_count'], $env_summary['data_count']]);
fputcsv($output, ['Social', $social_summary['kpi_count'], $social_summary['data_count']]);
fputcsv($output, ['Governance', $gov_summary['kpi_count'], $gov_summary['data_count']]);

fputcsv($output, []); // Empty row

// Footer text
fputcsv($output, ['This report was generated using Cenviro - ESG KPI Tracking System.']);

fclose($output);
exit;
?>
