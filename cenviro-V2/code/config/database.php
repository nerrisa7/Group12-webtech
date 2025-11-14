<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "sustainedge";

$conn = mysqli_connect($host, $username, $password);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$db_check = mysqli_select_db($conn, $database);
if (!$db_check) {
    mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS $database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    mysqli_select_db($conn, $database);
}

mysqli_set_charset($conn, "utf8");
?>

