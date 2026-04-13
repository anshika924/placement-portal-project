<?php
include 'config.php';
session_name("hod");
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: hod_login.php");
    exit;
}

$regdno = $_SESSION['r'] ?? '';
$sql = "SELECT * FROM package WHERE regdno = ?";
$row = []; 

if($regdno && $stmt = mysqli_prepare($conn, $sql)){
    mysqli_stmt_bind_param($stmt, "s", $regdno);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Placement Records | HOD Portal</title>
    
    <link rel="stylesheet" href="dashboard_revamp.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        .file-link-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: #f0fdf4;
            color: var(--brand-emerald);
            text-decoration: none;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            transition: all 0.2s;
            border: 1px solid #bbf7d0;
        }
        .file-link-btn:hover { background: var(--brand-emerald); color: white; transform: translateY(-2px); }
        .back-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            color: var(--text-primary);
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .back-link:hover { border-color: var(--brand-emerald); color: var(--brand-emerald); }
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
                <li class="nav-item active"><a href="#"><i class="fas fa-file-invoice-dollar"></i> Placement Status</a></li>
                <li class="nav-item"><a href="hodcompanylist.php"><i class="fas fa-users-viewfinder"></i> Eligibility Filter</a></li>
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
                    <h1 style="font-family: var(--font-heading); font-size: 24px; font-weight: 600;">Candidate Placement History</h1>
                    <p style="color: var(--text-secondary); margin-top: 4px;">Reviewing verified offers for: <span style="font-weight: 700; color: var(--text-primary);"><?php echo htmlspecialchars($regdno); ?></span></p>
                </div>
                
                <a href="hod_access2.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Return to Strategic View
                </a>
            </header>

            <!-- Record Module -->
            <div class="data-panel animate-up">
                <div class="data-header">
                    <h2>Verified Professional Offers</h2>
                </div>

                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Offer Index</th>
                            <th>Partner Enterprise</th>
                            <th>Package (LPA)</th>
                            <th>Authentication</th>
                            <th>Offer Letter</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($row)): ?>
                            <?php foreach($row as $rows): ?>
                            <tr>
                                <td style="font-family: monospace; color: var(--text-secondary);">#<?php echo htmlspecialchars($rows['id']); ?></td>
                                <td style="font-weight: 600; color: var(--text-primary);"><?php echo htmlspecialchars(ucfirst($rows['companyname'])); ?></td>
                                <td style="color: var(--brand-emerald); font-weight: 700;"><?php echo htmlspecialchars($rows['package']); ?> LPA</td>
                                <td><span class="status-badge status-placed">System Verified</span></td>
                                <td>
                                    <a href="uploaded_files/<?php echo $rows['file']; ?>" class="file-link-btn" target="_blank">
                                        <i class="fas fa-file-pdf"></i> View Annexure
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="padding: 60px; text-align: center; color: var(--text-secondary);">
                                    <i class="fas fa-search" style="font-size: 32px; margin-bottom: 20px; display: block; opacity: 0.1;"></i>
                                    No placement records detected for this candidate identifier.
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