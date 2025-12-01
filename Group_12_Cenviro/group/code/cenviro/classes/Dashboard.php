<?php
/*This class handles dashboard data retrieval.*/
class Dashboard {
    private $db;
    private $esgData;
    private $report;
    
    /*Constructor - needs database, ESGData, and Report objects*/
    public function __construct($database, $esgData, $report) {
        $this->db = $database;
        $this->esgData = $esgData;
        $this->report = $report;
    }
    
    /*Get all dashboard statistics for a user*/
    public function getDashboardStats($user_id) {
        return [
            'esg_score' => $this->report->getLatestEsgScore($user_id),
            'total_emissions' => $this->getTotalEmissions($user_id),
            'energy_usage' => $this->getEnergyUsage($user_id),
            'water_usage' => $this->getWaterUsage($user_id),
            'total_employees' => $this->getTotalEmployees($user_id),
            'gender_diversity' => $this->getGenderDiversity($user_id),
            'gov_compliant' => $this->getGovernanceCompliant($user_id),
            'recycling_rate' => $this->getRecyclingRate($user_id),
        ];
    }
    
    /*Get total emissions*/
    private function getTotalEmissions($user_id) {
        $conn = $this->db->getConnection();
        $user_id = (int)$user_id;
        
        $query = "SELECT 
            SUM(CASE WHEN k.kpi_name LIKE '%Scope 1%' OR k.kpi_name LIKE '%Emissions%' THEN e.value ELSE 0 END) as total_emissions
            FROM esg_data e
            JOIN esg_kpis k ON e.kpi_id = k.kpi_id
            WHERE e.user_id = '$user_id' AND e.category = 'environmental'
            AND (k.kpi_name LIKE '%Scope%' OR k.kpi_name LIKE '%Emissions%')";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return round($row['total_emissions'] ?? 0, 1);
        }
        return 0;
    }
    
    /*Get energy usage*/
    private function getEnergyUsage($user_id) {
        $value = $this->esgData->getLatestValue($user_id, 'Energy');
        return $value ? round($value, 0) : 0;
    }
    
    /*Get water usage*/
    private function getWaterUsage($user_id) {
        $value = $this->esgData->getLatestValue($user_id, 'Water');
        return $value ? round($value, 0) : 0;
    }
    
    /*Get total employees*/
    private function getTotalEmployees($user_id) {
        $value = $this->esgData->getLatestValue($user_id, 'Employees');
        return $value ? round($value, 0) : 0;
    }
    
    /*Get gender diversity percentage*/
    private function getGenderDiversity($user_id) {
        // The KPI name in database is "Diversity Index" (from data_input.php mapping)
        $value = $this->esgData->getLatestValue($user_id, 'Diversity Index');
        return $value ? round($value, 1) : 0;
    }
    
    /*Get governance compliance count - only counts Ethics Training KPI (Code of Conduct and Anti-Corruption)*/
    private function getGovernanceCompliant($user_id) {
        $conn = $this->db->getConnection();
        $user_id = (int)$user_id;
        
        // Only count "Ethics Training" KPI entries where value = 1 (Yes)
        // This represents Code of Conduct and Anti-Corruption Training compliance
        $query = "SELECT COUNT(*) as count FROM esg_data e 
                  JOIN esg_kpis k ON e.kpi_id = k.kpi_id 
                  WHERE e.user_id = '$user_id' 
                  AND e.category = 'governance' 
                  AND k.kpi_name = 'Ethics Training'
                  AND e.value = 1";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return $row['count'] ?? 0;
        }
        return 0;
    }
    
    /*Get recycling rate - calculated as percentage: (Recycled Waste / Total Waste) * 100*/
    private function getRecyclingRate($user_id) {
        $conn = $this->db->getConnection();
        $user_id = (int)$user_id;
        
        // Get latest Total Waste value
        $waste_kpi_id = $this->esgData->getKpiId('Waste Generated', 'environmental');
        $waste_query = "SELECT value FROM esg_data 
                       WHERE user_id = '$user_id' AND kpi_id = '$waste_kpi_id' 
                       ORDER BY created_at DESC LIMIT 1";
        $waste_result = mysqli_query($conn, $waste_query);
        $total_waste = 0;
        if ($waste_result && mysqli_num_rows($waste_result) > 0) {
            $row = mysqli_fetch_assoc($waste_result);
            $total_waste = (float)($row['value'] ?? 0);
        }
        
        // Get latest Recycled Waste value
        $recycled_value = $this->esgData->getLatestValue($user_id, 'Recycling');
        
        // Calculate percentage: (Recycled / Total) * 100
        if ($total_waste > 0 && $recycled_value) {
            $percentage = ($recycled_value / $total_waste) * 100;
            return round($percentage, 1);
        }
        
        return 0;
    }
    
    /*Get trend data for charts - returns data for last 6 months grouped by category*/
    public function getTrendData($user_id) {
        $conn = $this->db->getConnection();
        $user_id = (int)$user_id;
        
        // Get data from last 6 months
        $six_months_ago = date('Y-m-d', strtotime('-6 months'));
        
        // Get Environmental trend - average of all environmental KPIs per month
        $env_query = "SELECT DATE_FORMAT(e.period_date, '%Y-%m') as month, AVG(e.value) as avg_value
                     FROM esg_data e
                     JOIN esg_kpis k ON e.kpi_id = k.kpi_id
                     WHERE e.user_id = '$user_id' 
                     AND e.category = 'environmental'
                     AND e.period_date >= '$six_months_ago'
                     GROUP BY month
                     ORDER BY month";
        $env_result = mysqli_query($conn, $env_query);
        $env_data = [];
        $env_labels = [];
        while ($row = mysqli_fetch_assoc($env_result)) {
            $env_labels[] = date('M Y', strtotime($row['month'] . '-01'));
            $env_data[] = round($row['avg_value'], 1);
        }
        
        // Get Social trend - average of all social KPIs per month
        $social_query = "SELECT DATE_FORMAT(e.period_date, '%Y-%m') as month, AVG(e.value) as avg_value
                        FROM esg_data e
                        JOIN esg_kpis k ON e.kpi_id = k.kpi_id
                        WHERE e.user_id = '$user_id' 
                        AND e.category = 'social'
                        AND e.period_date >= '$six_months_ago'
                        GROUP BY month
                        ORDER BY month";
        $social_result = mysqli_query($conn, $social_query);
        $social_data = [];
        $social_labels = [];
        while ($row = mysqli_fetch_assoc($social_result)) {
            $social_labels[] = date('M Y', strtotime($row['month'] . '-01'));
            $social_data[] = round($row['avg_value'], 1);
        }
        
        // Get Governance trend - average of all governance KPIs per month
        $gov_query = "SELECT DATE_FORMAT(e.period_date, '%Y-%m') as month, AVG(e.value) as avg_value
                     FROM esg_data e
                     JOIN esg_kpis k ON e.kpi_id = k.kpi_id
                     WHERE e.user_id = '$user_id' 
                     AND e.category = 'governance'
                     AND e.period_date >= '$six_months_ago'
                     GROUP BY month
                     ORDER BY month";
        $gov_result = mysqli_query($conn, $gov_query);
        $gov_data = [];
        $gov_labels = [];
        while ($row = mysqli_fetch_assoc($gov_result)) {
            $gov_labels[] = date('M Y', strtotime($row['month'] . '-01'));
            $gov_data[] = round($row['avg_value'], 1);
        }
        
        return [
            'environmental' => ['labels' => $env_labels, 'data' => $env_data],
            'social' => ['labels' => $social_labels, 'data' => $social_data],
            'governance' => ['labels' => $gov_labels, 'data' => $gov_data]
        ];
    }
}
?>

