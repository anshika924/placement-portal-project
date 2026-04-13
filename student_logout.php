<?php
session_start();
$_SESSION = array();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Logout | Placement Portal</title>
    <meta http-equiv="refresh" content="2;url=student_login.php">
    
    <link rel="stylesheet" href="dashboard_revamp.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        body {
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            font-family: 'Inter', sans-serif;
        }
        .logout-container {
            text-align: center;
            padding: 40px;
            max-width: 400px;
            width: 90%;
            animation: fadeIn 0.8s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .success-icon {
            width: 80px;
            height: 80px;
            background: #f0fdf4;
            color: var(--brand-emerald);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin: 0 auto 24px;
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.1);
        }
        h1 {
            font-size: 24px;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        p {
            color: var(--text-secondary);
            font-size: 14px;
            line-height: 1.6;
        }
        .progress-bar {
            width: 100%;
            height: 4px;
            background: #f1f5f9;
            border-radius: 10px;
            margin-top: 32px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: var(--brand-emerald);
            width: 0%;
            animation: fillProgress 2s linear forwards;
        }
        @keyframes fillProgress {
            to { width: 100%; }
        }
    </style>
</head>
<body>

    <div class="logout-container">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h1>Securely Signed Out</h1>
        <p>Your session has been terminated successfully. You are being redirected to the login portal.</p>
        
        <div class="progress-bar">
            <div class="progress-fill"></div>
        </div>
        
        <a href="student_login.php" style="display:inline-block; margin-top: 24px; color: var(--brand-emerald); text-decoration: none; font-size: 13px; font-weight: 700;">
            Wait one moment... <i class="fas fa-arrow-right" style="margin-left: 5px;"></i>
        </a>
    </div>

</body>
</html>