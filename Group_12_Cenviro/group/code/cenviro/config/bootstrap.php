<?php
/*This file loads all the classes we need.*/

/*Load all class files*/
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Organization.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/ESGData.php';
require_once __DIR__ . '/../classes/Report.php';
require_once __DIR__ . '/../classes/Dashboard.php';
require_once __DIR__ . '/../classes/CompanySettings.php';

// Create database connection
$database = new Database();

// Create all the objects we'll need
$user = new User($database);
$organization = new Organization($database);
$esgData = new ESGData($database);
$report = new Report($database, $esgData);
$dashboard = new Dashboard($database, $esgData, $report);
$auth = new Auth($database, $user);
$companySettings = new CompanySettings($database);
?>
