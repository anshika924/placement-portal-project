<?php
include 'config.php';
session_name("staff");
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: staff_login.php");
    exit;
}

$backlog = $_POST['backlogs'] ?? 0;
$cgpa = $_POST['cgpa'] ?? 0;    
$company = $_POST['company'] ?? "General Search";

// Update session data for reports
$_SESSION["cgpa"] = $cgpa;
$_SESSION["backlog"] = $backlog;
$_SESSION["company"] = $company;

// Main data query
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

// Add company record if not exists
if ($company !== "General Search") {
    $checkCompany = "SELECT * FROM company WHERE companyname = ?";
    if ($stmt = mysqli_prepare($conn, $checkCompany)) {
        mysqli_stmt_bind_param($stmt, "s", $company);
        mysqli_stmt_execute($stmt);
        $res2 = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($res2) == 0) {
            $ins = "INSERT INTO company(companyname) VALUES(?)";
            if ($stmtIns = mysqli_prepare($conn, $ins)) {
                mysqli_stmt_bind_param($stmtIns, "s", $company);
                mysqli_stmt_execute($stmtIns);
            }
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Details | Staff Portal</title>
    
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
        .export-btn:hover {
            background: #f8fafc;
            border-color: var(--brand-emerald);
            color: var(--brand-emerald);
            transform: translateY(-2px);
        }
        .filter-summary {
            font-size: 13px;
            color: var(--text-secondary);
            font-weight: 600;
        }
        .filter-tag {
            background: #f1f5f9;
            padding: 4px 10px;
            border-radius: 6px;
            color: var(--text-primary);
            margin-right: 8px;
        }
    </style>
</head>
<body>

    <div class="dashboard-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i class="fas fa-shield-halved"></i>
                Admin Portal
            </div>

            <ul class="nav-links">
                <li class="nav-item"><a href="staff_access.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li class="nav-item"><a href="insertdata.php"><i class="fas fa-plus-circle"></i> Insert New Student</a></li>
                <li class="nav-item"><a href="updatedata.php"><i class="fas fa-pen-to-square"></i> Update Records</a></li>
                <li class="nav-item active"><a href="student_details.php"><i class="fas fa-user-group"></i> View Students</a></li>
                <li class="nav-item"><a href="generatefile.php"><i class="fas fa-file-chart-column"></i> Placement Reports</a></li>
                <li class="nav-item"><a href="staff_reset_password.php"><i class="fas fa-lock"></i> Security Settings</a></li>
            </ul>

            <div class="sidebar-footer">
                <a href="staff_logout.php" style="text-decoration: none;">
                    <div class="user-pill">
                        <img src="https://api.dicebear.com/7.x/shapes/svg?seed=Admin" alt="Admin Avatar">
                        <div style="flex: 1;">
                            <p style="font-size: 13px; font-weight: 700; color: var(--text-primary);">Admin Portal</p>
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
                    <h1 style="font-family: var(--font-heading); font-size: 24px; font-weight: 600;">Student Talent Database</h1>
                    <p style="color: var(--text-secondary); margin-top: 4px;">Review filtered placement candidates and academic performance.</p>
                </div>
                
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" id="quickSearch" placeholder="Filter current list...">
                </div>
            </header>

            <div class="data-panel animate-up">
                <div class="table-controls">
                    <div class="filter-summary">
                        Showing results for: 
                        <span class="filter-tag"><i class="fas fa-building"></i> <?php echo htmlspecialchars($company); ?></span>
                        <span class="filter-tag">CGPA &ge; <?php echo $cgpa; ?></span>
                        <span class="filter-tag">Backlogs &le; <?php echo $backlog; ?></span>
                    </div>
                    
                    <a href="generatefile.php" class="export-btn">
                        <i class="fas fa-file-export"></i> Export to CSV
                    </a>
                </div>

                <table class="custom-table" id="studentTable">
                    <thead>
                        <tr>
                            <th>Registration No</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Email Address</th>
                            <th>Date of Birth</th>
                            <th>Backlogs</th>
                            <th>CGPA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($students)): ?>
                            <?php foreach($students as $student): ?>
                            <tr>
                                <td style="font-weight: 700; color: var(--brand-emerald);"><?php echo htmlspecialchars($student['regdno']); ?></td>
                                <td style="font-weight: 600;"><?php echo htmlspecialchars($student['name']); ?></td>
                                <td><?php echo htmlspecialchars($student['contact']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td style="font-size: 12px; color: var(--text-secondary);"><?php echo htmlspecialchars($student['dob']); ?></td>
                                <td style="font-weight: 700; <?php echo $student['backlogs'] > 0 ? 'color: #ef4444;' : 'color: #22c55e;'; ?>">
                                    <?php echo htmlspecialchars($student['backlogs']); ?>
                                </td>
                                <td style="font-weight: 800; color: var(--text-primary);"><?php echo htmlspecialchars($student['cgpa']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                                    <i class="fas fa-folder-open" style="font-size: 24px; display: block; margin-bottom: 12px; opacity: 0.3;"></i>
                                    No records found matching these criteria.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
        </main>
    </div>

    <script>
        // Simple search filter for the table
        document.getElementById('quickSearch').addEventListener('keyup', function() {
            const term = this.value.toLowerCase();
            const rows = document.querySelectorAll('#studentTable tbody tr');
            
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(term) ? '' : 'none';
            });
        });
    </script>

</body>
</html>