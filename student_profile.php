<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: student_login.php");
    exit;
}
$regdno = $_SESSION["num"] ?? '';

// Fetch Student Profile
$student_data = null;
if ($regdno && isset($conn) && $conn instanceof mysqli) {
    try {
        $sql = "SELECT * FROM student WHERE regdno = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $regdno);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($result && $result->num_rows > 0) {
                $student_data = $result->fetch_assoc();
            }
            mysqli_stmt_close($stmt);
        }
    } catch (Throwable $e) { }
}

// Fetch Marks/Academic Data
$academic_data = null;
if ($regdno && isset($conn) && $conn instanceof mysqli) {
    try {
        $sql_marks = "SELECT * FROM marks WHERE regdno = ?";
        if ($stmt_m = mysqli_prepare($conn, $sql_marks)) {
            mysqli_stmt_bind_param($stmt_m, "s", $regdno);
            mysqli_stmt_execute($stmt_m);
            $result_marks = mysqli_stmt_get_result($stmt_m);
            if ($result_marks && $result_marks->num_rows > 0) {
                $academic_data = $result_marks->fetch_assoc();
            }
            mysqli_stmt_close($stmt_m);
        }
    } catch (Throwable $e) { }
}

if (!$student_data) {
    die("Error: Student record not found or database sync failed. Please contact administrator.");
}

// Fetch Official Offers (Dynamic Timeline)
$offers = [];
$offer_count = 0;
if ($regdno && isset($conn) && $conn instanceof mysqli) {
    try {
        $sql_offers = "SELECT * FROM package WHERE regdno = ? ORDER BY id DESC";
        if ($stmt_o = mysqli_prepare($conn, $sql_offers)) {
            mysqli_stmt_bind_param($stmt_o, "s", $regdno);
            mysqli_stmt_execute($stmt_o);
            $res_o = mysqli_stmt_get_result($stmt_o);
            
            // Manual fetch loop for maximum server compatibility
            while ($row = mysqli_fetch_assoc($res_o)) {
                $offers[] = $row;
            }
            $offer_count = count($offers);
            mysqli_stmt_close($stmt_o);
        }
    } catch (Throwable $e) { }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | Starline Placement</title>
    
    <link rel="stylesheet" href="dashboard_revamp.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        .timeline-item { padding-left: 30px; border-left: 2px solid #f1f5f9; position: relative; padding-bottom: 24px; }
        .timeline-item::before { content: ''; position: absolute; left: -5px; top: 0; width: 8px; height: 8px; background: var(--brand-emerald); border-radius: 50%; border: 2px solid white; z-index: 1; }
        .timeline-status { font-size: 10px; font-weight: 800; text-transform: uppercase; color: var(--brand-emerald); background: #f0fdf4; padding: 2px 8px; border-radius: 4px; margin-bottom: 6px; display: inline-block; }
        .timeline-title { font-weight: 700; margin-bottom: 4px; display: block; color: var(--text-primary); }
        .timeline-date { font-size: 11px; color: var(--text-secondary); }
        
        .widget-value.loading { color: transparent; background: #f1f5f9; border-radius: 6px; animation: pulse 1.5s infinite; }
        @keyframes pulse { 0% { opacity: 0.6; } 50% { opacity: 1; } 100% { opacity: 0.6; } }
    </style>
</head>
<body>

    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i class="fas fa-user-graduate"></i>
                Profile Node
            </div>

            <ul class="nav-links">
                <li class="nav-item active"><a href="student_profile.php"><i class="fas fa-th-large"></i> Overview</a></li>
                <li class="nav-item"><a href="student_drives.php"><i class="fas fa-briefcase"></i> Open Drives</a></li>
                <li class="nav-item"><a href="student_applications.php"><i class="fas fa-file-invoice"></i> Applications</a></li>
                <li class="nav-item"><a href="student_update_profile.php"><i class="fas fa-user-pen"></i> Update Profile</a></li>
                <li class="nav-item"><a href="student_reset_password.php"><i class="fas fa-lock"></i> Settings</a></li>
            </ul>

            <div class="sidebar-footer">
                <a href="student_logout.php" style="text-decoration: none;">
                    <div class="user-pill">
                        <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?php echo htmlspecialchars($student_data['name']); ?>" alt="User Avatar">
                        <div style="flex: 1;">
                            <p style="font-size: 13px; font-weight: 700; color: var(--text-primary);"><?php echo htmlspecialchars($student_data['name']); ?></p>
                            <p style="font-size: 11px; color: var(--text-secondary);">Sign Out</p>
                        </div>
                        <i class="fas fa-arrow-right-from-bracket" style="color: #ef4444; font-size: 14px;"></i>
                    </div>
                </a>
            </div>
        </aside>

        <main class="main-content">
            <header class="header animate-up">
                <div>
                    <h1 style="font-family: var(--font-heading); font-size: 24px; font-weight: 600;">Student Launchpad</h1>
                    <p style="color: var(--text-secondary); margin-top: 4px;">Welcome back. Monitor your career progression and academic metrics.</p>
                </div>
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search companies or jobs...">
                </div>
            </header>

            <div class="widget-grid animate-up" style="animation-delay: 0.1s;">
                <div class="widget-card">
                    <div class="widget-icon"><i class="fas fa-star"></i></div>
                    <div class="widget-value loading" id="cgpa-val"><?php echo $academic_data['cgpa'] ?? '0.0'; ?></div>
                    <div class="widget-label">Overall CGPA</div>
                </div>
                <div class="widget-card">
                    <div class="widget-icon"><i class="fas fa-triangle-exclamation"></i></div>
                    <div class="widget-value loading" id="backlog-val"><?php echo $academic_data['backlogs'] ?? '0'; ?></div>
                    <div class="widget-label">Backlogs</div>
                </div>
                <div class="widget-card">
                    <div class="widget-icon"><i class="fas fa-paper-plane"></i></div>
                    <div class="widget-value"><?php echo $offer_count; ?></div>
                    <div class="widget-label">Official Offers</div>
                </div>
                <div class="widget-card">
                    <div class="widget-icon"><i class="fas fa-shield-check"></i></div>
                    <div class="widget-value">85%</div>
                    <div class="widget-label">Profile Readiness</div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1.8fr 1.2fr; gap: 32px;" class="animate-up" style="animation-delay: 0.2s;">
                <div class="data-panel">
                    <div class="data-header">
                        <h2>Personal Records</h2>
                        <a href="update_placement.php" style="color: var(--brand-emerald); font-weight: 700; text-decoration: none; font-size: 12px; background: #f0fdf4; padding: 6px 14px; border-radius: 8px;">Upload Offer <i class="fas fa-arrow-up-from-bracket"></i></a>
                    </div>
                    
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Value</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Registration No</td>
                                <td style="font-weight: 600;"><?php echo htmlspecialchars($student_data['regdno']); ?></td>
                                <td><span class="status-badge status-placed">Verified</span></td>
                            </tr>
                            <tr>
                                <td>Full Legal Name</td>
                                <td style="font-weight: 600;"><?php echo htmlspecialchars($student_data['name']); ?></td>
                                <td><span class="status-badge status-placed">Official</span></td>
                            </tr>
                            <tr>
                                <td>University Email</td>
                                <td style="font-weight: 600;"><?php echo strtolower(explode(' ', $student_data['name'])[0]); ?>.star@edu.in</td>
                                <td><span class="status-badge status-pending">Primary</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="data-panel" style="max-height: 400px; overflow-y: auto;">
                    <div class="data-header"><h2>Placement Pulse</h2></div>
                    <?php if(!empty($offers)): ?>
                        <?php foreach($offers as $offer): ?>
                        <div class="timeline-item">
                            <span class="timeline-status">Verified Offer</span>
                            <span class="timeline-title"><?php echo htmlspecialchars($offer['companyname']); ?></span>
                            <span class="timeline-date"><?php echo htmlspecialchars($offer['package']); ?> LPA Offer</span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 20px; color: var(--text-secondary);">
                            <p style="font-size: 12px;">No active placement records found.</p>
                        </div>
                    <?php endif; ?>
                    <div class="timeline-item" style="border: none;">
                        <span class="timeline-status" style="color: #64748b; background: #f1f5f9;">Registration</span>
                        <span class="timeline-title">Profile Synchronized</span>
                        <span class="timeline-date">Account Active</span>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/countup.js/1.9.3/countUp.min.js"></script>
    <script>
        function initCounters() {
            if (typeof CountUp === 'undefined') {
                setTimeout(initCounters, 100);
                return;
            }
            const cgpaVal = document.getElementById('cgpa-val');
            const backlogVal = document.getElementById('backlog-val');
            const cgpa = parseFloat(cgpaVal.innerText) || 0;
            const backlogs = parseInt(backlogVal.innerText) || 0;
            
            cgpaVal.classList.remove('loading');
            backlogVal.classList.remove('loading');
            new CountUp('cgpa-val', 0, cgpa, 2, 2.5).start();
            new CountUp('backlog-val', 0, backlogs, 0, 2).start();
        }
        window.onload = initCounters;
    </script>
</body>
</html>