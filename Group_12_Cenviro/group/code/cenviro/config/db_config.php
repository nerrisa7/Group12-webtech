<?php
/*Database configuration - update these for your server*/


$is_server = (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] !== 'localhost');

if ($is_server) {

    define('DB_HOST', 'localhost');
    define('DB_USERNAME', 'davis.amponsah');  
    define('DB_PASSWORD', '77dzS=6wRP');  
    define('DB_NAME', 'webtech_2025A_davis_amponsah');  
} else {
    // LOCAL SETTINGS
    define('DB_HOST', 'localhost');
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', '');
    define('DB_NAME', 'sustainedge');
}
?>

