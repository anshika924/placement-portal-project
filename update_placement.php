<?php
require_once 'config.php';

// Detect Role (Try Staff Session first, then Student)
$is_staff = false;
$is_student = false;
$user_name = "User";

// Attempt to hook into Staff session
session_name("staff");
session_start();

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    if (isset($_SESSION["role"]) && $_SESSION["role"] === "admin") {
        $is_staff = true;
        $user_name = $_SESSION["username"] ?? "Administrator";
    }
}

// If not a staff, close and try student session
if (!$is_staff) {
    session_write_close();
    session_name("PHPSESSID"); // Student uses default session name
    // Avoid double start if already in student session context (e.g. from local test)
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
        if (isset($_SESSION["num"])) {
            $is_student = true;
            $user_name = $_SESSION["username"] ?? $_SESSION["num"];
        }
    }
}

// Redirect if still not logged in
if (!$is_staff && !$is_student) {
    header("location: student_login.php");
    exit;
}

// Handle Admin Direct Update (POST from dashboard)
$message = "";
$message_type = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && $is_staff && isset($_POST['regdno'])) {
    $regdno = mysqli_real_escape_string($conn, $_POST['regdno']);
    $company = mysqli_real_escape_string($conn, $_POST['company']);
    $package = mysqli_real_escape_string($conn, $_POST['package']);
    
    // VALIDATION: Check if student exists
    $check_sql = "SELECT regdno FROM student WHERE regdno = '$regdno'";
    $check_res = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_res) > 0) {
        // Proceed with insertion
        $sql = "INSERT INTO package (regdno, companyname, package, file) VALUES ('$regdno', '$company', '$package', 'VERIFIED_BY_ADMIN.pdf')";
        try {
            if (mysqli_query($conn, $sql)) {
                $message = "Placement record for student $regdno has been formally secured in the registry.";
                $message_type = "success";
            }
        } catch (Exception $e) {
            $message = "Conflict Detected: A record for this student and company already exists in the infrastructure.";
            $message_type = "error";
        }
    } else {
        $message = "Validation Failed: Student ID '$regdno' does not exist in our central directory.";
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Placement Verification | Starline</title>
    
    <link rel="stylesheet" href="dashboard_revamp.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        .status-container {
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
        }
        .success-icon { font-size: 64px; color: var(--brand-emerald); margin-bottom: 24px; animation: scaleUp 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .error-icon { font-size: 64px; color: #ef4444; margin-bottom: 24px; animation: shake 0.5s; }
        
        @keyframes scaleUp { from { transform: scale(0); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        @keyframes shake { 0%, 100% { transform: translateX(0); } 20%, 60% { transform: translateX(-10px); } 40%, 80% { transform: translateX(10px); } }

        .form-panel { max-width: 500px; margin: 0 auto; }
        .input-group { margin-bottom: 24px; text-align: left; }
        .input-group label { display: block; font-size: 13px; font-weight: 700; color: var(--text-secondary); margin-bottom: 8px; text-transform: uppercase; }
        .input-group input { width: 100%; padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 14px; font-family: inherit; }
        .input-group input:focus { outline: none; border-color: var(--brand-emerald); box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1); }
        
        .submit-btn { width: 100%; padding: 14px; background: var(--brand-emerald); color: white; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; text-transform: uppercase; margin-top: 24px; transition: 0.3s; }
        .submit-btn:hover { background: #059669; transform: translateY(-2px); box-shadow: 0 10px 15px rgba(16, 185, 129, 0.2); }
    </style>
</head>
<body>

    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-logo"><i class="fas fa-shield-halved"></i> Admin Portal</div>
            <ul class="nav-links">
                <li class="nav-item"><a href="staff_access.php"><i class="fas fa-home"></i> Back to Hub</a></li>
                <li class="nav-item active"><a href="#"><i class="fas fa-file-upload"></i> Placement Status</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="header animate-up">
                <div>
                    <h1 style="font-family: var(--font-heading); font-size: 24px; font-weight: 600;">Infrastructure Verification</h1>
                    <p style="color: var(--text-secondary); margin-top: 4px;">Processing official recruitment records and system state updates.</p>
                </div>
            </header>

            <div class="data-panel animate-up" style="padding: 60px;">
                
                <?php if($message): ?>
                    <div class="status-container">
                        <div class="<?php echo $message_type == 'success' ? 'success-icon' : 'error-icon'; ?>">
                            <i class="fas <?php echo $message_type == 'success' ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                        </div>
                        <h2 style="font-size: 20px; font-weight: 600; margin-bottom: 12px; color: var(--text-primary);">
                            <?php echo $message_type == 'success' ? 'Update Finalized' : 'System Rejection'; ?>
                        </h2>
                        <p style="color: var(--text-secondary); margin-bottom: 32px; font-size: 15px; line-height: 1.6;"><?php echo $message; ?></p>
                        <a href="staff_access.php" class="submit-btn" style="text-decoration: none; display: inline-block; width: auto; padding: 12px 32px;">Return to Dashboard</a>
                    </div>
                <?php else: ?>
                    <div class="form-panel">
                        <div class="data-header"><h2>Submit Offer Records</h2></div>
                        <form action="upload.php" method="post" enctype="multipart/form-data">
                            <div class="input-group"><label>Reference ID</label><input type="text" name="id" placeholder="e.g. OFF-2024-101" required></div>
                            <div class="input-group"><label>Partner Enterprise</label><input type="text" name="title" placeholder="e.g. Google" required></div>
                            <div class="input-group"><label>Annual Package (LPA)</label><input type="number" name="pac" step="0.1" placeholder="e.g. 15.5" required></div>
                            <button type="submit" name="submit" class="submit-btn">Authorize Placement</button>
                        </form>
                    </div>
                <?php endif; ?>

            </div>
        </main>
    </div>
</body>
</html>