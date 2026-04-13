<?php
/**
 * Database credentials - Placement Portal Management
 */
$host = "localhost";
$user = "root";
$password = "";

// Smart DB Detection: Try placement_portal_new first, then fallback to miniproject
$databases = ["placement_portal_new", "miniproject", "placement_portal"];
$conn = false;
$active_db = "NONE";

foreach ($databases as $db_name) {
    $c = @mysqli_connect($host, $user, $password, $db_name);
    if ($c) {
        $conn = $c;
        $active_db = $db_name;
        break;
    }
}

// Final fallback: Connection without DB to prevent fatal crashes
if($conn === false){
    $conn = mysqli_connect($host, $user, $password);
    if ($conn === false) {
        die("ERROR: Could not connect to MySQL server. Ensure XAMPP is running.");
    }
}

// Set global for diagnostic messaging
$_GLOBALS['current_active_db'] = $active_db;
?>