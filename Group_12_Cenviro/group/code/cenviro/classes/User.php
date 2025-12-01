<?php
/*This class handles all user-related operations.*/
class User {
    private $db;
    
    /*Constructor - needs a database connection*/
    public function __construct($database) {
        $this->db = $database;
    }
    
    /*Get user information by user ID*/
    public function getUserById($user_id) {
        $conn = $this->db->getConnection();
        $user_id = mysqli_real_escape_string($conn, $user_id);
        
        $query = "SELECT u.*, o.org_name 
                  FROM users u 
                  LEFT JOIN organizations o ON u.org_id = o.org_id 
                  WHERE u.user_id = '$user_id'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
        return null;
    }
    
    /*Get user information by email*/
    public function getUserByEmail($email) {
        $conn = $this->db->getConnection();
        $email = mysqli_real_escape_string($conn, $email);
        
        $query = "SELECT u.*, o.org_name 
                  FROM users u 
                  LEFT JOIN organizations o ON u.org_id = o.org_id 
                  WHERE u.email = '$email'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
        return null;
    }
    
    /*Check if email already exists*/
    public function emailExists($email) {
        $conn = $this->db->getConnection();
        $email = mysqli_real_escape_string($conn, $email);
        
        $query = "SELECT user_id FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $query);
        
        return ($result && mysqli_num_rows($result) > 0);
    }
    
    /*Create a new user*/   
    public function createUser($name, $email, $password, $org_id) {
        $conn = $this->db->getConnection();
        $name = mysqli_real_escape_string($conn, $name);
        $email = mysqli_real_escape_string($conn, $email);
        $hashed_password = md5($password); // Hash the password
        $org_id = (int)$org_id;
        
        $query = "INSERT INTO users (name, email, password, org_id) 
                  VALUES ('$name', '$email', '$hashed_password', '$org_id')";
        
        if (mysqli_query($conn, $query)) {
            return mysqli_insert_id($conn);
        }
        return false;
    }
}
?>

