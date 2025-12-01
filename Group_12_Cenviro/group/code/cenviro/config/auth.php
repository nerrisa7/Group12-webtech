<?php
/*This file loads the bootstrap and provides helper functions for backward compatibility.*/

// Load all classes
require_once __DIR__ . '/bootstrap.php';

    /*Helper functions for backward compatibility*/

function requireLogin() {
    global $auth;
    $auth->requireLogin();
}

function getCurrentUser($conn = null) {
    global $auth;
    return $auth->getCurrentUser();
}

function loginUser($conn, $email, $password) {
    global $auth;
    return $auth->login($email, $password);
}

function logoutUser() {
    global $auth;
    $auth->logout();
}
?>

