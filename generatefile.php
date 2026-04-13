require_once "config.php";

$is_staff = false;
$is_hod = false;

// Attempt Staff Session
session_name("staff");
session_start();
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    $is_staff = true;
} else {
    session_write_close();
    // Attempt HOD Session
    session_name("hod");
    session_start();
    if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
        $is_hod = true;
    }
}

// Security check: Redirect if neither role is verified
if (!$is_staff && !$is_hod) {
    header("location: staff_login.php");
    exit;
}

// Fetch filter criteria from session
$cgpa = $_SESSION["cgpa"] ?? 0;
$backlog = $_SESSION["backlog"] ?? 99;
$company_name = $_SESSION['company'] ?? "General_Report";

// Optimized Query: JOIN student and marks in one go
$sql = "SELECT s.regdno, s.name, s.email, s.contact, s.dob, m.cgpa, m.backlogs 
        FROM student s 
        JOIN marks m ON s.regdno = m.regdno 
        WHERE m.cgpa >= ? AND m.backlogs <= ? 
        ORDER BY m.cgpa DESC";

$filename = "Placement_Report_" . str_replace(' ', '_', $company_name) . "_" . date('Y-m-d') . ".csv";

// Prepare CSV Headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

// Set professional headers
fputcsv($output, array('Registration No', 'Full Name', 'University Email', 'Contact Number', 'Date of Birth', 'CGPA', 'Active Backlogs', 'Target Company'));

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "di", $cgpa, $backlog);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($result)) {
        // Add the target company to every row for clarity
        $row['company'] = $company_name;
        fputcsv($output, $row);
    }
    
    mysqli_stmt_close($stmt);
}

fclose($output);
exit;
?>