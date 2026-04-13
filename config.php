<?php
/**
 * Database credentials - Placement Portal Management
 * Refactored for Cloud Deployment
 */

// Use Environment Variables for Production, with local fallbacks for XAMPP
$host     = getenv('DB_HOST')     ?: "localhost";
$user     = getenv('DB_USER')     ?: "root";
$password = getenv('DB_PASSWORD') ?: "";
$dbname   = getenv('DB_NAME')     ?: "placement_portal_new";

// List of databases to try (for legacy support)
$databases = [$dbname, "miniproject", "placement_portal"];
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
    $conn = @mysqli_connect($host, $user, $password);
    if ($conn === false) {
        // Only die if we are not in a serverless environment where DB might be lazy-loaded
        if (!getenv('VERCEL') && !getenv('RAILWAY_STATIC_URL')) {
            die("ERROR: Could not connect to MySQL server. Ensure your Database is running.");
        }
    }
}

// Set global for diagnostic messaging
$_GLOBALS['current_active_db'] = $active_db;
?>