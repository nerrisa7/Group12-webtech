<?php
/*This class handles all ESG (Environmental, Social, Governance) data operations.*/
class ESGData {
    private $db;
    
    /*Constructor - needs a database connection*/
    public function __construct($database) {
        $this->db = $database;
    }
    
    /*Get KPI ID by name pattern and category*/
    public function getKpiId($name_pattern, $category) {
        $conn = $this->db->getConnection();
        $pattern = mysqli_real_escape_string($conn, $name_pattern);
        $category = mysqli_real_escape_string($conn, $category);
        
        $query = "SELECT kpi_id FROM esg_kpis 
                  WHERE category = '$category' AND kpi_name LIKE '%$pattern%' 
                  LIMIT 1";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return $row['kpi_id'];
        }
        return null;
    }
    
    /*Save ESG data for a user*/
    public function saveData($user_id, $kpi_id, $category, $value, $period_date) {
        $conn = $this->db->getConnection();
        $user_id = (int)$user_id;
        $kpi_id = (int)$kpi_id;
        $category = mysqli_real_escape_string($conn, $category);
        $value = mysqli_real_escape_string($conn, $value);
        $period_date = mysqli_real_escape_string($conn, $period_date);
        
        $query = "INSERT INTO esg_data (user_id, kpi_id, category, value, period_date) 
                  VALUES ('$user_id', '$kpi_id', '$category', '$value', '$period_date')
                  ON DUPLICATE KEY UPDATE value = '$value', updated_at = NOW()";
        
        return mysqli_query($conn, $query);
    }
    
    /*Get value from KPI by name*/      
    public function getValueFromKpi($user_id, $kpi_name, $period_start, $period_end) {
        $conn = $this->db->getConnection();
        $start = mysqli_real_escape_string($conn, $period_start);
        $end = mysqli_real_escape_string($conn, $period_end);
        $name = mysqli_real_escape_string($conn, $kpi_name);
        $user_id = (int)$user_id;
        
        $query = "SELECT AVG(e.value) as val FROM esg_data e 
                  JOIN esg_kpis k ON e.kpi_id = k.kpi_id 
                  WHERE e.user_id = {$user_id} AND k.kpi_name = '{$name}' 
                  AND e.period_date BETWEEN '{$start}' AND '{$end}'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return (float)($row['val'] ?? 0);
        }
        return null;
    }
    
    /*Get data summary for a user by category*/
    public function getDataSummary($user_id, $category) {
        $conn = $this->db->getConnection();
        $user_id = (int)$user_id;
        $category = mysqli_real_escape_string($conn, $category);
        
        $query = "SELECT COUNT(DISTINCT kpi_id) as kpi_count, COUNT(*) as data_count 
                  FROM esg_data 
                  WHERE user_id = '$user_id' AND category = '$category'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
        return ['kpi_count' => 0, 'data_count' => 0];
    }
    
    /*Get latest value for a specific KPI pattern*/
    public function getLatestValue($user_id, $kpi_pattern) {
        $conn = $this->db->getConnection();
        $pattern = mysqli_real_escape_string($conn, $kpi_pattern);
        $user_id = (int)$user_id;
        
        $query = "SELECT e.value FROM esg_data e 
                  JOIN esg_kpis k ON e.kpi_id = k.kpi_id 
                  WHERE e.user_id = '$user_id' AND k.kpi_name LIKE '%$pattern%' 
                  ORDER BY e.created_at DESC LIMIT 1";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return $row['value'] ?? null;
        }
        return null;
    }
    
}
?>

