<?php
session_start();
require_once "config.php";

// Standardize Security Headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Redirect if already logged in
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: student_profile.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])){
    // Hardened 3-field authentication
    $regdno = strtolower(trim($_POST['regdno'] ?? ''));
    $uname = trim($_POST['uname'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($regdno) && !empty($uname) && !empty($password)) {
        // Prepare case-insensitive search query
        $sql = "SELECT regdno, name, password FROM student WHERE LOWER(regdno) = ? AND name = ? AND password = ?";
    
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "sss", $regdno, $uname, $password);
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    mysqli_stmt_bind_result($stmt, $res_regdno, $res_name, $res_password);
                    if(mysqli_stmt_fetch($stmt)){
                        // Authentication Success
                        $_SESSION["loggedin"] = true;
                        $_SESSION["username"] = $res_name;
                        $_SESSION["num"] = $res_regdno;
                        header("location: student_profile.php");
                        exit();
                    }
                } else {
                    $error = "Incorrect credentials. Searching in: <strong>" . ($_GLOBALS['current_active_db'] ?? 'DB') . "</strong>. Please verify your details.";
                }
            } else {
                $error = "Server error. Please try again.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = "Database lookup error. Please check your connectivity.";
        }
    } else {
        $error = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login | Premium Placement Portal</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --emerald: #10b981;
            --emerald-dark: #059669;
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-300: #cbd5e1;
            --slate-400: #94a3b8;
            --slate-500: #64748b;
            --slate-600: #475569;
            --slate-700: #334155;
            --slate-800: #1e293b;
            --slate-900: #0f172a;
            --white: #ffffff;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--white);
            height: 100vh;
            display: flex;
            overflow: hidden;
        }

        /* Split Screen UI - Matching HOD/Admin */
        .visual-side {
            width: 45%;
            background: #020617;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 80px;
            text-align: center;
        }

        .blob {
            position: absolute;
            width: 700px; height: 700px;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.25;
            z-index: 1;
        }
        .blob-1 { top: -200px; left: -200px; background: var(--emerald); animation: fluid 20s infinite alternate ease-in-out; }
        .blob-2 { bottom: -200px; right: -200px; background: #3b82f6; animation: fluid 25s infinite alternate-reverse ease-in-out; }

        @keyframes fluid {
            0% { transform: translate(0, 0) scale(1) rotate(0deg); }
            100% { transform: translate(100px, 50px) scale(1.1) rotate(15deg); }
        }

        .visual-footer { position: relative; z-index: 10; color: white; max-width: 440px; }
        .visual-footer h3 { font-size: 40px; font-weight: 800; line-height: 1.1; margin-bottom: 24px; letter-spacing: -1px; }
        .visual-footer p { font-size: 18px; color: var(--slate-400); line-height: 1.6; }

        .auth-side {
            width: 55%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px;
            background: var(--white);
        }

        .auth-container { width: 100%; max-width: 440px; animation: slideIn 0.8s ease-out; }
        @keyframes slideIn { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }

        .brand-icon {
            width: 56px; height: 56px;
            background: #f0fdf4;
            color: var(--emerald);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 40px;
            font-size: 26px;
        }

        .auth-header { margin-bottom: 44px; }
        .auth-header h1 { font-size: 34px; font-weight: 800; color: var(--slate-900); margin-bottom: 12px; letter-spacing: -0.5px; }
        .auth-header p { font-size: 16px; color: var(--slate-500); }

        .form-group { margin-bottom: 24px; width: 100%; position: relative; }
        .form-group label { display: block; font-size: 14px; font-weight: 700; color: var(--slate-800); margin-bottom: 10px; }
        .form-group i { position: absolute; left: 16px; top: 43px; color: var(--slate-400); font-size: 14px; }
        
        .form-group input {
            width: 100%;
            padding: 14px 16px 14px 44px;
            background: #fcfcfc;
            border: 1.5px solid var(--slate-100);
            border-radius: 12px;
            font-size: 15px;
            color: var(--slate-900);
            transition: all 0.2s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--emerald);
            background: var(--white);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }

        .btn-submit {
            width: 100%;
            padding: 16px;
            background: var(--emerald);
            color: var(--white);
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.4);
        }

        .btn-submit:hover { transform: translateY(-2px); background: var(--emerald-dark); box-shadow: 0 15px 30px -5px rgba(16, 185, 129, 0.5); }

        .error-msg {
            width: 100%;
            background: #fef2f2;
            color: #ef4444;
            padding: 14px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 28px;
            border: 1px solid #fee2e2;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .help-link { margin-top: 32px; font-size: 14px; color: var(--slate-500); text-align: center; }
        .help-link a { color: var(--slate-900); font-weight: 700; text-decoration: none; border-bottom: 1.5px solid var(--slate-200); padding-bottom: 2px; }

        @media (max-width: 1100px) {
            .visual-side { display: none; }
            .auth-side { width: 100%; }
        }
    </style>
</head>
<body>

    <div class="visual-side">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="visual-footer">
            <h3>Empowering Your Path.</h3>
            <p>Access your personalized dashboard and track recruitment real-time.</p>
        </div>
    </div>

    <div class="auth-side">
        <div class="auth-container">
            <div class="brand-icon"><i class="fas fa-graduation-cap"></i></div>
            
            <div class="auth-header">
                <h1>Student Login</h1>
                <p>Welcome back. Please authenticate to continue.</p>
            </div>

            <?php if(!empty($error)): ?>
                <div class="error-msg">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label>Registration Number</label>
                    <i class="far fa-id-badge"></i>
                    <input type="text" name="regdno" placeholder="e.g. 19331a1201" required>
                </div>

                <div class="form-group">
                    <label>Full Name</label>
                    <i class="far fa-user"></i>
                    <input type="text" name="uname" placeholder="Your registered name" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>

                <button type="submit" name="submit" class="btn-submit">Authenticate Portal</button>
            </form>

            <div class="help-link">
                Trouble logging in? <a href="#">Contact Support</a>
            </div>
        </div>
    </div>

</body>
</html>