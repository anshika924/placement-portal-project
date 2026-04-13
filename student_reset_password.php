<?php
session_start();
require_once "config.php";

// Check if the user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: student_login.php");
    exit;
}

$new_password = $confirm_password = "";
$new_password_err = $confirm_password_err = "";
$success_msg = "";
$num = $_SESSION["num"];

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
        // Security: Using Prepared Statement with Parameter Binding
        $sql = "UPDATE student SET password = ? WHERE regdno = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "ss", $new_password, $num);
            if(mysqli_stmt_execute($stmt)){
                // Success - Destroy session and force re-login for security
                $success_msg = "Password updated successfully. Redirecting to login...";
                session_destroy();
                header("refresh:3;url=student_login.php");
            } else{
                $confirm_password_err = "Critical Error: Could not update security records.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Settings | Student Portal</title>
    
    <link rel="stylesheet" href="dashboard_revamp.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        .security-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 40px;
            border: 1px solid #f1f5f9;
            max-width: 500px;
        }
        .security-warning {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
            padding: 16px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 32px;
            display: flex;
            gap: 12px;
            line-height: 1.5;
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
                <li class="nav-item"><a href="student_applications.php"><i class="fas fa-file-invoice"></i> Applications</a></li>
                <li class="nav-item"><a href="student_update_profile.php"><i class="fas fa-user-pen"></i> Update Profile</a></li>
                <li class="nav-item active"><a href="student_reset_password.php"><i class="fas fa-lock"></i> Settings</a></li>
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
                    <h1 style="font-family:var(--font-heading); font-size:24px; font-weight:600;">Security Portal</h1>
                    <p style="color:var(--text-secondary); margin-top:4px;">Protect your account with high-entropy credential management.</p>
                </div>
            </header>

            <div style="margin-top:40px;">
                <?php if($success_msg): ?>
                    <div style="background:#f0fdf4; color:var(--brand-emerald); padding:20px; border-radius:15px; margin-bottom:32px; font-weight:700; border: 1px solid #dcfce7; max-width:500px;">
                        <i class="fas fa-check-double"></i> <?php echo $success_msg; ?>
                    </div>
                <?php endif; ?>

                <div class="security-card animate-up">
                    <div class="security-warning">
                        <i class="fas fa-shield-halved" style="font-size:18px; margin-top:2px;"></i>
                        <div>
                            <strong>Security Notice</strong><br>
                            Changing your password will immediately terminate your current session. You will need to re-authenticate with your new credentials.
                        </div>
                    </div>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="input-group">
                            <label>New Entropy-Password</label>
                            <input type="password" name="new_password" required placeholder="Minimum 6 characters">
                            <?php if($new_password_err): ?>
                                <p style="color:#ef4444; font-size:11px; margin-top:6px; font-weight:600;"><?php echo $new_password_err; ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="input-group" style="margin-top:24px;">
                            <label>Confirm Global Credential</label>
                            <input type="password" name="confirm_password" required placeholder="Must match new password">
                            <?php if($confirm_password_err): ?>
                                <p style="color:#ef4444; font-size:11px; margin-top:6px; font-weight:600;"><?php echo $confirm_password_err; ?></p>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="submit-btn" style="width:100%; margin-top:40px; border-radius:12px;">Update Security Credentials</button>
                    </form>
                </div>
            </div>
        </main>
    </div>

</body>
</html>