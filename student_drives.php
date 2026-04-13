<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: student_login.php");
    exit;
}

// Fetch all companies
$companies = [];
if ($conn) {
    $sql = "SELECT * FROM company ORDER BY companyname ASC";
    $result = $conn->query($sql);
    if ($result) {
        $companies = $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Open Drives | Student Portal</title>
    
    <link rel="stylesheet" href="dashboard_revamp.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        .drive-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid #f1f5f9;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .drive-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px -8px rgba(0,0,0,0.05);
            border-color: var(--brand-emerald);
        }
        .company-logo {
            width: 54px; height: 54px;
            background: #f8fafc;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: var(--brand-emerald);
        }
    </style>
</head>
<body>

    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-logo"><i class="fas fa-user-graduate"></i> Profile Node</div>
            <ul class="nav-links">
                <li class="nav-item"><a href="student_profile.php"><i class="fas fa-th-large"></i> Overview</a></li>
                <li class="nav-item active"><a href="student_drives.php"><i class="fas fa-briefcase"></i> Open Drives</a></li>
                <li class="nav-item"><a href="student_applications.php"><i class="fas fa-file-invoice"></i> Applications</a></li>
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
                    <h1 style="font-family:var(--font-heading); font-size:24px; font-weight:600;">Active Recruitment Drives</h1>
                    <p style="color:var(--text-secondary); margin-top:4px;">Explore current opportunities from top-tier enterprise partners.</p>
                </div>
            </header>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px; margin-top:32px;" class="animate-up">
                <?php if(!empty($companies)): ?>
                    <?php foreach($companies as $company): ?>
                        <div class="drive-card">
                            <div class="company-logo"><i class="fas fa-building"></i></div>
                            <div style="flex:1;">
                                <h3 style="font-size:16px; font-weight:700; color:var(--text-primary); margin-bottom:4px;"><?php echo htmlspecialchars(ucfirst($company['companyname'])); ?></h3>
                                <p style="font-size:12px; color:var(--text-secondary);">Actively recruiting for entry-level positions.</p>
                            </div>
                            <a href="#" style="color:var(--brand-emerald); font-size:18px;"><i class="fas fa-arrow-right-circle"></i></a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color:var(--text-secondary);">No active drives found.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

</body>
</html>
