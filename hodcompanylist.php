<?php
include 'config.php';
session_name("hod");
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: hod_login.php");
    exit;
}

$backlog = $_POST['backlogs'] ?? 0;
$cgpa = $_POST['cgpa'] ?? 0;    
$company = $_POST['company'] ?? "General Selection";

// Update session data
$_SESSION["cgpa"] = $cgpa;
$_SESSION["backlog"] = $backlog;
$_SESSION["company"] = $company;

// Refactored Optimized Query: Single JOIN instead of loop queries
$sql = "SELECT m.regdno, s.name, s.contact, s.email, s.dob, m.backlogs, m.cgpa 
        FROM marks m 
        JOIN student s ON m.regdno = s.regdno 
        WHERE m.cgpa >= ? AND m.backlogs <= ?";

$students = [];
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "di", $cgpa, $backlog);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $students = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eligibility List | HOD Portal</title>
    
    <link rel="stylesheet" href="dashboard_revamp.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        .table-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .export-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            color: var(--text-primary);
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            transition: all 0.2s;
        }
        .export-btn:hover { background: #f8fafc; border-color: var(--brand-emerald); color: var(--brand-emerald); transform: translateY(-2px); }
        .filter-tag { background: #f0fdf4; padding: 4px 12px; border-radius: 6px; font-size: 12px; font-weight: 700; color: var(--brand-emerald); margin-right: 8px; border: 1px solid #dcfce7; }
    </style>
</head>
<body>

    <div class="dashboard-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i class="fas fa-chart-line-up"></i>
                HOD Portal
            </div>

            <ul class="nav-links">
                <li class="nav-item"><a href="hod_access2.php"><i class="fas fa-magnifying-glass-chart"></i> Strategic Overview</a></li>
                <li class="nav-item active"><a href="#"><i class="fas fa-users-viewfinder"></i> Eligibility Filter</a></li>
                <li class="nav-item"><a href="hod_studentDetails.php"><i class="fas fa-file-invoice-dollar"></i> Placement Status</a></li>
                <li class="nav-item"><a href="generatefile.php"><i class="fas fa-chart-pie"></i> Analytics & Reports</a></li>
                <li class="nav-item"><a href="hod_reset_password.php"><i class="fas fa-key"></i> Security Portal</a></li>
            </ul>

            <div class="sidebar-footer">
                <a href="hod_logout.php" style="text-decoration: none;">
                    <div class="user-pill">
                        <img src="https://api.dicebear.com/7.x/shapes/svg?seed=HOD" alt="HOD Avatar">
                        <div style="flex: 1;">
                            <p style="font-size: 13px; font-weight: 700; color: var(--text-primary);">HOD Portal</p>
                            <p style="font-size: 11px; color: var(--text-secondary);">Sign Out</p>
                        </div>
                        <i class="fas fa-arrow-right-from-bracket" style="color: #ef4444; font-size: 14px;"></i>
                    </div>
                </a>
            </div>
        </aside>

        <!-- Main Workspace -->
        <main class="main-content">
            <header class="header animate-up">
                <div>
                    <h1 style="font-family: var(--font-heading); font-size: 24px; font-weight: 600;">Talent Selection List</h1>
                    <p style="color: var(--text-secondary); margin-top: 4px;">Identifying eligible candidates for <span style="font-weight: 700; color: var(--text-primary);"><?php echo htmlspecialchars($company); ?></span>.</p>
                </div>
                
                <a href="generatefile.php" class="export-btn">
                    <i class="fas fa-file-export"></i> Export Report
                </a>
            </header>

            <div class="data-panel animate-up">
                <div class="table-controls">
                    <div>
                        <span class="filter-tag">CGPA &ge; <?php echo $cgpa; ?></span>
                        <span class="filter-tag">Backlogs &le; <?php echo $backlog; ?></span>
                    </div>
                </div>

                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Enterprise</th>
                            <th>Registration No</th>
                            <th>Full Name</th>
                            <th>Contact</th>
                            <th>Email Address</th>
                            <th>Backlogs</th>
                            <th>CGPA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($students)): ?>
                            <?php foreach($students as $student): ?>
                            <tr>
                                <td style="font-weight: 600; text-transform: uppercase; font-size: 11px; color: var(--text-secondary);"><?php echo htmlspecialchars($company); ?></td>
                                <td style="font-weight: 700; color: var(--brand-emerald);"><?php echo htmlspecialchars($student['regdno']); ?></td>
                                <td style="font-weight: 600;"><?php echo htmlspecialchars($student['name']); ?></td>
                                <td><?php echo htmlspecialchars($student['contact']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td style="font-weight: 700; <?php echo $student['backlogs'] > 0 ? 'color: #ef4444;' : 'color: #10b981;'; ?>">
                                    <?php echo htmlspecialchars($student['backlogs']); ?>
                                </td>
                                <td style="font-weight: 800; color: var(--text-primary);"><?php echo htmlspecialchars($student['cgpa']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="padding: 60px; text-align: center; color: var(--text-secondary);">
                                    <i class="fas fa-users-slash" style="font-size: 32px; margin-bottom: 20px; display: block; opacity: 0.1;"></i>
                                    No candidates meet the specified eligibility criteria for this drive.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>

</body>
</html>
<?php mysqli_close($conn); ?>