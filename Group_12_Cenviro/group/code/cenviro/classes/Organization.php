<?php
/*This class handles organization-related operations.*/
class Organization {
    private $db;
    
    /*Constructor - needs a database connection*/
    public function __construct($database) {
        $this->db = $database;
    }
    
    /*Create a new organization*/
    public function createOrganization($org_name) {
        $conn = $this->db->getConnection();
        $org_name = mysqli_real_escape_string($conn, $org_name);
        
        $query = "INSERT INTO organizations (org_name) VALUES ('$org_name')";
        
        if (mysqli_query($conn, $query)) {
            return mysqli_insert_id($conn);
        }
        return false;
    }
    
    /*Get organization by ID*/
    public function getOrganizationById($org_id) {
        $conn = $this->db->getConnection();
        $org_id = (int)$org_id;
        
        $query = "SELECT * FROM organizations WHERE org_id = '$org_id'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
        return null;
    }
    
    /*Update organization information*/
    public function updateOrganization($org_id, $org_name, $logo_path = null, $company_info = null) {
        $conn = $this->db->getConnection();
        $org_id = (int)$org_id;
        $org_name = mysqli_real_escape_string($conn, $org_name);
        
        $updates = ["org_name = '$org_name'"];
        
        if ($logo_path !== null) {
            $logo_path = mysqli_real_escape_string($conn, $logo_path);
            $updates[] = "logo_path = '$logo_path'";
        }
        
        if ($company_info !== null) {
            $company_info = mysqli_real_escape_string($conn, $company_info);
            $updates[] = "company_info = '$company_info'";
        }
        
        $query = "UPDATE organizations SET " . implode(', ', $updates) . " WHERE org_id = '$org_id'";
        
        return mysqli_query($conn, $query);
    }
}
?>

