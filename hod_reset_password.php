<?php
session_name("hod");
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: hod_login.php");
    exit;
}

require_once "config.php";

$new_password = $confirm_password = "";
$new_password_err = $confirm_password_err = "";
$success_msg = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    if(empty(trim($_POST["new_password"]))){
        $new_password_err = "Please enter the new password.";     
    } elseif(strlen(trim($_POST["new_password"])) < 6){
        $new_password_err = "Password must have at least 6 characters.";
    } else{
        $new_password = trim($_POST["new_password"]);
    }
    
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm the password.";
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($new_password_err) && ($new_password != $confirm_password)){
            $confirm_password_err = "Passwords do not match.";
        }
    }
        
    if(empty($new_password_err) && empty($confirm_password_err)){
        $sql = "UPDATE staff SET password = ? WHERE staff_name = 'hod'";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $new_password);
            if(mysqli_stmt_execute($stmt)){
                $success_msg = "Security credentials updated. Re-authentication required in 3 seconds.";
                header("refresh:3; url=hod_logout.php");
            } else{
                $new_password_err = "Oops! Something went wrong. Please try again.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Portal | HOD Portal</title>
    
    <link rel="stylesheet" href="dashboard_revamp.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        .setting-panel {
            max-width: 500px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 24px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: var(--text-secondary);
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .form-group input {
            width: 100%;
            padding: 14px 16px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            font-family: inherit;
            color: var(--text-primary);
            transition: all 0.2s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #ef4444; 
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
        }
        .invalid-feedback {
            color: #ef4444;
            font-size: 12px;
            font-weight: 600;
            margin-top: 6px;
        }
        .alert-success {
            background: #f0fdf4;
            color: #166534;
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 32px;
            font-size: 14px;
            font-weight: 600;
            border: 1px solid #bbf7d0;
        }
        .submit-btn {
            width: 100%;
            padding: 16px;
            background: var(--brand-emerald);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }
        .submit-btn:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.2);
        }
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
                <li class="nav-item"><a href="hod_studentDetails.php"><i class="fas fa-file-invoice-dollar"></i> Placement Status</a></li>
                <li class="nav-item"><a href="hodcompanylist.php"><i class="fas fa-users-viewfinder"></i> Eligibility Filter</a></li>
                <li class="nav-item"><a href="generatefile.php"><i class="fas fa-chart-pie"></i> Analytics & Reports</a></li>
                <li class="nav-item active"><a href="hod_reset_password.php"><i class="fas fa-key"></i> Security Portal</a></li>
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

        <!-- Main Content -->
        <main class="main-content">
            <header class="header animate-up">
                <div>
                    <h1 style="font-family: var(--font-heading); font-size: 24px; font-weight: 600;">Security Governance</h1>
                    <p style="color: var(--text-secondary); margin-top: 4px;">Managing departmental authentication and high-level access keys.</p>
                </div>
            </header>

            <div class="data-panel animate-up" style="max-width: 600px; margin: 0 auto; padding: 48px;">
                <div class="data-header">
                    <h2>Update Access Credentials</h2>
                </div>

                <?php if($success_msg): ?>
                    <div class="alert-success"><i class="fas fa-check-circle"></i> <?php echo $success_msg; ?></div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="setting-panel">
                    <div class="form-group">
                        <label>New HOD Password</label>
                        <input type="password" name="new_password" placeholder="Min. 6 alphanumeric characters" required>
                        <div class="invalid-feedback"><?php echo $new_password_err; ?></div>
                    </div>

                    <div class="form-group">
                        <label>Confirm HOD Password</label>
                        <input type="password" name="confirm_password" placeholder="Re-enter to confirm" required>
                        <div class="invalid-feedback"><?php echo $confirm_password_err; ?></div>
                    </div>

                    <p style="font-size: 12px; color: var(--text-secondary); margin-bottom: 32px; line-height: 1.6;">
                        <i class="fas fa-shield-halved"></i> Updating your password will invalidate your current session to ensure system integrity.
                    </p>

                    <button type="submit" class="submit-btn">
                        Apply Security Update
                    </button>
                </form>
            </div>
            
        </main>
    </div>

</body>
</html>