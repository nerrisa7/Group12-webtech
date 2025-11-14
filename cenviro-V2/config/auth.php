<?php
session_start();
require_once __DIR__ . '/database.php';

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

function getCurrentUser($conn) {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    $user_id = $_SESSION['user_id'];
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

function loginUser($conn, $email, $password) {
    $email = mysqli_real_escape_string($conn, $email);
    $query = "SELECT u.*, o.org_name 
              FROM users u 
              LEFT JOIN organizations o ON u.org_id = o.org_id 
              WHERE u.email = '$email'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if (md5($password) === $user['password']) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['org_name'] = $user['org_name'];
            return $user;
        }
    }
    return false;
}

function logoutUser() {
    session_destroy();
    header('Location: index.php');
    exit();
}
?>

