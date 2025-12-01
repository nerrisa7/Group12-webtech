<?php
/*This class handles company settings operations.*/
class CompanySettings {
    private $db;
    
    /*Constructor - needs a database connection*/
    public function __construct($database) {
        $this->db = $database;
    }
    
    /*Get company settings (the single row with id = 1)*/
    /*Get company settings (the single row with id = 1)*/
    public function getSettings() {
        $conn = $this->db->getConnection();
        $query = "SELECT * FROM company_settings WHERE id = 1";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
        
        $this->createDefaultSettings();
        
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
        
        return null;
    }
    
    /*Create default settings row*/
    private function createDefaultSettings() {
        $conn = $this->db->getConnection();
        $query = "INSERT INTO company_settings (id, company_name) VALUES (1, 'My Company')";
        mysqli_query($conn, $query);
    }
    
    /*Save company settings (INSERT if new, UPDATE if exists)*/
    public function updateSettings($data) {
        $conn = $this->db->getConnection();
        
        // Check if row exists
        $check_query = "SELECT id FROM company_settings WHERE id = 1";
        $check_result = mysqli_query($conn, $check_query);
        $row_exists = ($check_result && mysqli_num_rows($check_result) > 0);
        
        if ($row_exists) {
            // Row exists, UPDATE it
            $updates = [];
            foreach ($data as $field => $value) {
                $field = mysqli_real_escape_string($conn, $field);
                $value = mysqli_real_escape_string($conn, $value);
                $updates[] = "`$field` = '$value'";
            }
            
            if (empty($updates)) {
                return false;
            }
            
            $query = "UPDATE company_settings SET " . implode(', ', $updates) . " WHERE id = 1";
            return mysqli_query($conn, $query);
        } else {
            // Row doesn't exist, INSERT it
            // id will automatically be set to 1 because of DEFAULT 1 in the table
            $fields = [];
            $values = [];
            
            foreach ($data as $field => $value) {
                $field = mysqli_real_escape_string($conn, $field);
                $value = mysqli_real_escape_string($conn, $value);
                $fields[] = "`$field`";
                $values[] = "'$value'";
            }
            
            $query = "INSERT INTO company_settings (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $values) . ")";
            return mysqli_query($conn, $query);
        }
    }
}
?>

