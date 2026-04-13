<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: student_login.php");
    exit;
}

$regdno = $_SESSION["num"] ?? '';
$success_msg = "";
$error_msg = "";

// Fetch current data
$student = [];
if ($regdno && $conn) {
    try {
        $sql = "SELECT * FROM student WHERE regdno = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $regdno);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $student = mysqli_fetch_assoc($res);
            mysqli_stmt_close($stmt);
        }
    } catch (Throwable $e) {}
}

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact']);
    
    if (!empty($name) && !empty($email)) {
        $update_sql = "UPDATE student SET name = ?, email = ?, contact = ? WHERE regdno = ?";
        if ($stmt = mysqli_prepare($conn, $update_sql)) {
            mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $contact, $regdno);
            if (mysqli_stmt_execute($stmt)) {
                $success_msg = "Profile updated successfully.";
                $_SESSION["username"] = $name; // Update session name
                $student['name'] = $name;
                $student['email'] = $email;
                $student['contact'] = $contact;
            } else {
                $error_msg = "Database error. Failed to update profile.";
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $error_msg = "Name and Email are required fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Management | Student Portal</title>
    
    <link rel="stylesheet" href="dashboard_revamp.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        .profile-header-card {
            background: linear-gradient(135deg, var(--brand-emerald), var(--brand-emerald-dark));
            border-radius: 20px;
            padding: 40px;
            color: white;
            display: flex;
            align-items: center;
            gap: 32px;
            margin-bottom: 40px;
            box-shadow: 0 20px 40px -12px rgba(16, 185, 129, 0.4);
        }
        .profile-avatar-large {
            width: 100px; height: 100px;
            border-radius: 50%;
            border: 4px solid rgba(255,255,255,0.2);
            background: white;
        }
        .form-section {
            background: white;
            padding: 40px;
            border-radius: 20px;
            border: 1px solid #f1f5f9;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        }
        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
        }
        @media (max-width: 800px) { .form-grid-2 { grid-template-columns: 1fr; } }
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
                <li class="nav-item active"><a href="student_update_profile.php"><i class="fas fa-user-pen"></i> Update Profile</a></li>
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
                    <h1 style="font-family:var(--font-heading); font-size:24px; font-weight:600;">Account Settings</h1>
                    <p style="color:var(--text-secondary); margin-top:4px;">Keep your student profile updated for recruitment synchronization.</p>
                </div>
            </header>

            <div class="profile-header-card animate-up">
                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?php echo htmlspecialchars($student['name'] ?? 'User'); ?>" class="profile-avatar-large" alt="Avatar">
                <div>
                    <h2 style="font-size:32px; font-weight:800; margin-bottom:4px;"><?php echo htmlspecialchars($student['name'] ?? 'Incomplete Profile'); ?></h2>
                    <p style="opacity:0.8; font-size:15px;">Reg No: <?php echo htmlspecialchars($student['regdno'] ?? 'N/A'); ?> • Batch of 2024</p>
                </div>
            </div>

            <?php if($success_msg): ?>
                <div style="background:#f0fdf4; color:var(--brand-emerald); padding:20px; border-radius:15px; margin-bottom:32px; font-weight:700; border: 1px solid #dcfce7; display:flex; align-items:center; gap:12px;" class="animate-up">
                    <i class="fas fa-check-double"></i> <?php echo $success_msg; ?>
                </div>
            <?php endif; ?>
            <?php if($error_msg): ?>
                <div style="background:#fef2f2; color:#ef4444; padding:20px; border-radius:15px; margin-bottom:32px; font-weight:700; border: 1px solid #fee2e2; display:flex; align-items:center; gap:12px;" class="animate-up">
                    <i class="fas fa-shield-slash"></i> <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <div class="form-section animate-up">
                <h3 style="font-size:18px; font-weight:700; margin-bottom:32px; color:var(--text-primary); border-bottom:1px solid #f1f5f9; padding-bottom:16px;">Core Identity</h3>
                <form method="POST" action="">
                    <div class="form-grid-2">
                        <div class="input-group">
                            <label>Registration Number (Immutable)</label>
                            <input type="text" value="<?php echo htmlspecialchars($student['regdno'] ?? ''); ?>" readonly style="background:#f8fafc; color:#94a3b8; border-color:#e2e8f0; cursor:not-allowed;">
                        </div>
                        <div class="input-group">
                            <label>Date of Birth (System Lock)</label>
                            <input type="text" value="<?php echo htmlspecialchars($student['dob'] ?? ''); ?>" readonly style="background:#f8fafc; color:#94a3b8; border-color:#e2e8f0; cursor:not-allowed;">
                        </div>
                        <div class="input-group">
                            <label>Full Legal Name</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($student['name'] ?? ''); ?>" required placeholder="Enter full name">
                        </div>
                        <div class="input-group">
                            <label>Official Email Address</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($student['email'] ?? ''); ?>" required placeholder="name@university.com">
                        </div>
                        <div class="input-group" style="grid-column: span 2;">
                            <label>Primary Contact Number</label>
                            <input type="text" name="contact" value="<?php echo htmlspecialchars($student['contact'] ?? ''); ?>" placeholder="+91 00000 00000">
                        </div>
                    </div>
                    
                    <div style="margin-top:48px; border-top:1px solid #f1f5f9; padding-top:32px; display:flex; justify-content:flex-end;">
                        <button type="submit" name="update" class="submit-btn" style="padding: 14px 40px; font-weight:800; letter-spacing:0.5px;">Update Profile Information</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

</body>
</html>
