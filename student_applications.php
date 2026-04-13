<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: student_login.php");
    exit;
}

$regdno = $_SESSION["num"] ?? '';
$applications = [];

if ($regdno && $conn) {
    try {
        $sql = "SELECT * FROM package WHERE regdno = ? ORDER BY id DESC";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $regdno);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) {
                $applications[] = $row;
            }
            mysqli_stmt_close($stmt);
        }
    } catch (Throwable $e) {}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications | Student Portal</title>
    
    <link rel="stylesheet" href="dashboard_revamp.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        .app-row {
            background: #ffffff;
            border-radius: 12px;
            padding: 16px 24px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 1px solid #f1f5f9;
        }
        .file-link {
            padding: 8px 16px;
            background: #f0fdf4;
            color: var(--brand-emerald);
            border-radius: 8px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
        }
    </style>
</head>
<body>

    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-logo"><i class="fas fa-user-graduate"></i> Profile Node</div>
            <ul class="nav-links">
                <li class="nav-item"><a href="student_profile.php"><i class="fas fa-th-large"></i> Overview</a></li>
                <li class="nav-item"><a href="student_drives.php"><i class="fas fa-briefcase"></i> Open Drives</a></li>
                <li class="nav-item active"><a href="student_applications.php"><i class="fas fa-file-invoice"></i> Applications</a></li>
                <li class="nav-item"><a href="student_update_profile.php"><i class="fas fa-user-pen"></i> Update Profile</a></li>
                <li class="nav-item"><a href="student_reset_password.php"><i class="fas fa-lock"></i> Settings</a></li>
            </ul>
            <div class="sidebar-footer">
                <a href="student_logout.php" style="text-decoration:none;">
                    <div class="user-pill">
                        <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?php echo htmlspecialchars($_SESSION['username'] ?? 'Student'); ?>" alt="User Avatar">
                        <div style="flex:1;">
                            <p style="font-size:13px; font-weight:700; color:var(--text-primary);"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Student'); ?></p>
                            <p style="font-size:11px; color:var(--text-secondary);">Sign Out</p>
                        </div>
                        <i class="fas fa-arrow-right-from-bracket" style="color:#ef4444; font-size:14px;"></i>
                    </div>
                </a>
            </div>
        </aside>

        <main class="main-content">
            <header class="header">
                <div>
                    <h1 style="font-family:var(--font-heading); font-size:24px; font-weight:600;">Application History</h1>
                    <p style="color:var(--text-secondary); margin-top:4px;">Manage your official offers and recruitment documents.</p>
                </div>
            </header>

            <div style="margin-top:32px;">
                <?php if(!empty($applications)): ?>
                    <?php foreach($applications as $app): ?>
                        <div class="app-row animate-up">
                            <div style="display:flex; align-items:center; gap:16px;">
                                <div style="width:40px; height:40px; background:#f8fafc; border-radius:10px; display:flex; align-items:center; justify-content:center; color:var(--brand-emerald);">
                                    <i class="fas fa-file-check"></i>
                                </div>
                                <div>
                                    <p style="font-weight:700; font-size:14px;"><?php echo htmlspecialchars($app['companyname']); ?></p>
                                    <p style="font-size:11px; color:var(--text-secondary);"><?php echo htmlspecialchars($app['package']); ?> LPA Offer</p>
                                </div>
                            </div>
                            <a href="<?php echo htmlspecialchars($app['file']); ?>" class="file-link" target="_blank">
                                <i class="fas fa-download"></i> View Document
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align:center; padding: 60px 0;">
                        <i class="fas fa-folder-open" style="font-size:48px; color:#e2e8f0; margin-bottom:16px;"></i>
                        <p style="color:var(--text-secondary);">You have no placement records or active applications.</p>
                        <a href="update_placement.php" style="color:var(--brand-emerald); font-weight:700; text-decoration:none; margin-top:12px; display:inline-block;">Upload your first offer</a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

</body>
</html>
