<?php
/*This class handles ESG report generation and calculations.*/
class Report {
    private $db;
    private $esgData;
    
    /*Constructor - needs database and ESGData objects*/
    public function __construct($database, $esgData) {
        $this->db = $database;
        $this->esgData = $esgData;
    }
    
    /*Get ESG metrics with their ideal values*/
    public function getEsgMetrics() {
        return [
            ['name' => 'Energy Consumption Intensity', 'ideal' => 200, 'direction' => 'lower_better'],
            ['name' => 'Carbon Emissions per Employee', 'ideal' => 5, 'direction' => 'lower_better'],
            ['name' => 'Water Usage Intensity', 'ideal' => 50, 'direction' => 'lower_better'],
            ['name' => 'Water Recycling Rate', 'ideal' => 50, 'direction' => 'higher_better'],
            ['name' => 'Waste Recycling Rate', 'ideal' => 70, 'direction' => 'higher_better'],
            ['name' => 'Employee Turnover Rate', 'ideal' => 10, 'direction' => 'lower_better'],
            ['name' => 'Women Representation', 'ideal' => 40, 'direction' => 'higher_better'],
            ['name' => 'Training Hours per Employee', 'ideal' => 20, 'direction' => 'higher_better'],
            ['name' => 'Injury Rate', 'ideal' => 1, 'direction' => 'lower_better'],
            ['name' => 'Ethics Training Coverage', 'ideal' => 100, 'direction' => 'higher_better'],
        ];
    }
    
    /*Calculate ESG score from benchmarks*/
    public function calculateEsgScore($user_id, $period_start, $period_end) {
        $metrics = $this->getEsgMetrics();
        $scores = [];
        
        foreach ($metrics as $metric) {
            $actual = $this->getMetricActualValue($user_id, $metric, $period_start, $period_end);
            if ($actual !== null) {
                $metric['actual'] = $actual;
                $scores[] = $this->normalizeBenchmarkMetric($metric);
            }
        }
        
        if (empty($scores)) {
            return 0;
        }
        
        $average = array_sum($scores) / count($scores);
        return round($average * 100, 1);
    }
    
    /*Normalize a metric value to 0-1 scale based on benchmark*/
    private function normalizeBenchmarkMetric($metric) {
        $direction = $metric['direction'] ?? 'higher_better';
        $ideal = isset($metric['ideal']) ? (float)$metric['ideal'] : 0;
        $actual = isset($metric['actual']) ? (float)$metric['actual'] : null;
        
        if ($actual === null) {
            return 0;
        }
        
        if ($direction === 'lower_better') {
            if ($actual <= 0) {
                return 1;
            }
            return max(0, min(1, $ideal / max($actual, 0.00001)));
        }
        
        if ($direction === 'binary') {
            return $actual == $ideal ? 1 : 0;
        }
        
        if ($ideal <= 0) {
            return 1;
        }
        
        return max(0, min(1, $actual / $ideal));
    }
    
    /*Get actual value for a metric*/
    private function getMetricActualValue($user_id, $metric, $period_start, $period_end) {
        if (!$period_start || !$period_end) {
            return null;
        }
        
        $metric_name = $metric['name'] ?? '';
        
        switch ($metric_name) {
            case 'Energy Consumption Intensity':
                return $this->calculateEnergyConsumptionIntensity($user_id, $period_start, $period_end);
            case 'Carbon Emissions per Employee':
                return $this->calculateCarbonEmissionsPerEmployee($user_id, $period_start, $period_end);
            case 'Water Usage Intensity':
                return $this->calculateWaterUsageIntensity($user_id, $period_start, $period_end);
            case 'Water Recycling Rate':
                return $this->esgData->getValueFromKpi($user_id, 'Recycling Rate', $period_start, $period_end);
            case 'Waste Recycling Rate':
                return $this->esgData->getValueFromKpi($user_id, 'Recycling Rate', $period_start, $period_end);
            case 'Employee Turnover Rate':
                return $this->calculateEmployeeTurnoverRate($user_id, $period_start, $period_end);
            case 'Training Hours per Employee':
                return $this->calculateTrainingHoursPerEmployee($user_id, $period_start, $period_end);
            case 'Injury Rate':
                return $this->calculateInjuryRate($user_id, $period_start, $period_end);
            case 'Women Representation':
                return $this->esgData->getValueFromKpi($user_id, 'Diversity Index', $period_start, $period_end);
            case 'Ethics Training Coverage':
                return $this->esgData->getValueFromKpi($user_id, 'Ethics Training', $period_start, $period_end);
        }
        
        return null;
    }
    
    /*Calculate energy consumption intensity*/
    private function calculateEnergyConsumptionIntensity($user_id, $period_start, $period_end) {
        $energy = $this->esgData->getValueFromKpi($user_id, 'Energy Consumption', $period_start, $period_end);
        $employees = $this->esgData->getValueFromKpi($user_id, 'Total Employees', $period_start, $period_end);
        
        if ($energy && $employees && $employees > 0) {
            $months = max(1, (strtotime($period_end) - strtotime($period_start)) / (30 * 24 * 3600));
            return $energy / $employees / $months;
        }
        return null;
    }
    
    /*Calculate carbon emissions per employee*/
    private function calculateCarbonEmissionsPerEmployee($user_id, $period_start, $period_end) {
        $carbon = $this->esgData->getValueFromKpi($user_id, 'Carbon Emissions', $period_start, $period_end);
        $employees = $this->esgData->getValueFromKpi($user_id, 'Total Employees', $period_start, $period_end);
        
        if ($carbon && $employees && $employees > 0) {
            return $carbon / $employees;
        }
        return null;
    }
    
    /*Calculate water usage intensity*/
    private function calculateWaterUsageIntensity($user_id, $period_start, $period_end) {
        $water = $this->esgData->getValueFromKpi($user_id, 'Water Usage', $period_start, $period_end);
        $employees = $this->esgData->getValueFromKpi($user_id, 'Total Employees', $period_start, $period_end);
        
        if ($water && $employees && $employees > 0) {
            return $water / $employees;
        }
        return null;
    }
    
    /*Calculate employee turnover rate*/
    private function calculateEmployeeTurnoverRate($user_id, $period_start, $period_end) {
        $leaving = $this->esgData->getValueFromKpi($user_id, 'Employee Turnover', $period_start, $period_end);
        $employees = $this->esgData->getValueFromKpi($user_id, 'Total Employees', $period_start, $period_end);
        
        if ($leaving && $employees && $employees > 0) {
            return ($leaving / $employees) * 100;
        }
        return null;
    }
    
    /*Calculate training hours per employee*/
    private function calculateTrainingHoursPerEmployee($user_id, $period_start, $period_end) {
        $training = $this->esgData->getValueFromKpi($user_id, 'Training Hours', $period_start, $period_end);
        $employees = $this->esgData->getValueFromKpi($user_id, 'Total Employees', $period_start, $period_end);
        
        if ($training && $employees && $employees > 0) {
            return $training / $employees;
        }
        return null;
    }
    
    /*Calculate injury rate*/
    private function calculateInjuryRate($user_id, $period_start, $period_end) {
        $injuries = $this->esgData->getValueFromKpi($user_id, 'Safety Incidents', $period_start, $period_end);
        $hours = $this->esgData->getValueFromKpi($user_id, 'Training Hours', $period_start, $period_end);
        
        if ($injuries && $hours && $hours > 0) {
            return ($injuries * 200000) / $hours;
        }
        return null;
    }
    
    /*Get latest ESG score for a user*/
    public function getLatestEsgScore($user_id) {
        $conn = $this->db->getConnection();
        $user_id = (int)$user_id;
        
        $query = "SELECT esg_score FROM reports 
                  WHERE user_id = '$user_id' 
                  ORDER BY generated_date DESC LIMIT 1";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return $row['esg_score'] ?? 0;
        }
        return 0;
    }
    
    /*Generate and save a new report*/
    public function generateReport($user_id, $period_start, $period_end) {
        $conn = $this->db->getConnection();
        $user_id = (int)$user_id;
        $period_start = mysqli_real_escape_string($conn, $period_start);
        $period_end = mysqli_real_escape_string($conn, $period_end);
        
        $esg_score = $this->calculateEsgScore($user_id, $period_start, $period_end);
        $status = 'complete';
        $generated_date = date('Y-m-d H:i:s');
        
        $query = "INSERT INTO reports (user_id, period_start, period_end, esg_score, generated_date, status) 
                  VALUES ('$user_id', '$period_start', '$period_end', '$esg_score', '$generated_date', '$status')";
        
        return mysqli_query($conn, $query);
    }
    
    /*Get all reports for a user*/
    public function getUserReports($user_id, $limit = 20) {
        $conn = $this->db->getConnection();
        $user_id = (int)$user_id;
        $limit = (int)$limit;
        
        $query = "SELECT report_id, period_start, period_end, esg_score, generated_date, status 
                  FROM reports 
                  WHERE user_id = '$user_id' 
                  ORDER BY generated_date DESC 
                  LIMIT $limit";
        $result = mysqli_query($conn, $query);
        
        $reports = [];
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $reports[] = $row;
            }
        }
        return $reports;
    }
}
?>

