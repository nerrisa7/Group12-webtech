<?php
/*It's checks if users are logged in and manages their sessions.*/
class Auth {
    private $db;
    private $user;
    
    /**
     * Constructor - needs database and user objects
     */
    public function __construct($database, $user) {
        $this->db = $database;
        $this->user = $user;
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /*Check if user is logged in*/
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    /*Require user to be logged in and redirects to login page if not logged in*/
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
    }
    
    /*Get the currently logged in user*/
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return $this->user->getUserById($_SESSION['user_id']);
    }
    
    /*Login a user*/    
    public function login($email, $password) {
        $user = $this->user->getUserByEmail($email);
        
        if ($user && md5($password) === $user['password']) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['org_name'] = $user['org_name'];
            
            return $user;
        }
        
        return false;
    }
    
    /*Logout the current user*/
    public function logout() {
        session_destroy();
        header('Location: index.php');
    }
}
?>
