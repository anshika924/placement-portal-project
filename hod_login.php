<?php
require_once "config.php";
session_name("hod");
session_start();

// Redirect if already logged in
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: hod_access2.php");
    exit;
}

$error = "";

if (isset($_POST['submit'])){
    $uname = trim($_POST['uname']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    $sql = "SELECT staff_name, password, role FROM staff WHERE staff_name = ? AND password = ? AND role = ?";
    
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "sss", $param_uname, $param_password, $param_role);
        
        $param_uname = $uname;
        $param_password = $password; 
        $param_role = $role;

        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_store_result($stmt);
            
            if(mysqli_stmt_num_rows($stmt) == 1){
                mysqli_stmt_bind_result($stmt, $res_uname, $res_password, $res_role);
                if(mysqli_stmt_fetch($stmt)){
                    if ($res_role == "hod") {
                        $_SESSION["loggedin"] = true;
                        $_SESSION["role"] = $res_role;
                        $_SESSION["username"] = $res_uname; 
                        header("location: hod_access2.php");
                        exit();
                    } else {
                        $error = "Unauthorized role for this portal.";
                    }
                }
            } else {
                $error = "Invalid credentials. Please try again.";
            }
        } else {
            $error = "System error. Please try again later.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $error = "Database error: Unable to connect. Check your config.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOD Login | Placement Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --emerald: #10b981;
            --emerald-dark: #059669;
            --forest: #064e3b;
            --darker: #020617;
            --slate-500: #64748b;
            --slate-900: #0f172a;
            --white: #ffffff;
        }

        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; background: var(--white); height: 100vh; display: flex; overflow: hidden; }

        .visual-side {
            width: 40%;
            background: radial-gradient(circle at center, var(--forest) 0%, var(--darker) 100%);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px;
            text-align: center;
        }

        .blob {
            position: absolute;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.6;
            z-index: 1;
        }
        .blob-1 { top: -150px; left: -150px; background: var(--emerald); animation: fluidRotate 18s infinite alternate ease-in-out; }
        .blob-2 { bottom: -150px; right: -150px; background: var(--emerald-dark); animation: fluidRotate 22s infinite alternate-reverse ease-in-out; animation-delay: -5s; }

        @keyframes fluidRotate {
            0% { transform: translate(0, 0) scale(1) rotate(0deg); }
            33% { transform: translate(120px, -50px) scale(1.1) rotate(60deg); }
            66% { transform: translate(-40px, 120px) scale(0.9) rotate(-40deg); }
            100% { transform: translate(80px, 40px) scale(1.05) rotate(20deg); }
        }

        .visual-footer { position: relative; z-index: 10; color: white; max-width: 400px; }
        .visual-footer h3 { font-size: 36px; font-weight: 800; line-height: 1.1; margin-bottom: 24px; }

        .auth-side {
            width: 60%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px;
        }

        .auth-container { width: 100%; max-width: 440px; }
        .brand-icon { 
            width: 54px; height: 54px; background: #f0fdf4; border-radius: 14px; 
            display: flex; align-items: center; justify-content: center; margin-bottom: 36px;
            color: var(--emerald); font-size: 26px;
        }

        .auth-header { margin-bottom: 40px; text-align: left; width: 100%; }
        .auth-header h1 { font-size: 34px; font-weight: 800; color: var(--slate-900); margin-bottom: 12px; }
        .auth-header p { font-size: 16px; color: var(--slate-500); }

        .form-group { margin-bottom: 24px; width: 100%; position: relative; }
        .form-group label { display: block; font-size: 14px; font-weight: 700; color: var(--slate-800); margin-bottom: 10px; }
        .form-group i { position: absolute; left: 16px; top: 43px; color: var(--slate-500); font-size: 14px; }
        .form-group input { 
            width: 100%; padding: 14px 16px 14px 44px; border: 1.5px solid #edf2f7; border-radius: 12px; 
            font-size: 15px; color: var(--slate-900); transition: all 0.2s; background: #fcfcfc;
        }
        .form-group input:focus { outline: none; border-color: var(--emerald); background: white; box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1); }

        .btn-submit { 
            width: 100%; padding: 16px; border-radius: 12px; border: none; font-size: 16px; font-weight: 800; 
            cursor: pointer; transition: all 0.3s; color: white;
            background: var(--emerald);
            box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.4); margin-bottom: 24px;
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        .btn-submit:hover { transform: translateY(-2px); background: var(--emerald-dark); }

        .error-msg { width: 100%; background: #fef2f2; color: #ef4444; padding: 14px; border-radius: 10px; font-size: 14px; margin-bottom: 24px; border: 1px solid #fee2e2; }

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
            <h3>Departmental Leadership.</h3>
            <p>Access specialized recruitment tools and analyze departmental placement success.</p>
        </div>
    </div>

    <div class="auth-side">
        <div class="auth-container">
            <div class="brand-icon"><i class="fas fa-chart-line"></i></div>
            
            <div class="auth-header">
                <h1>HOD Login</h1>
                <p>Welcome back, Professor.</p>
            </div>

            <?php if(!empty($error)): ?>
                <div class="error-msg"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label>Access Role</label>
                    <i class="fas fa-user-tag"></i>
                    <input type="text" name="role" value="hod" readonly required>
                </div>

                <div class="form-group">
                    <label>Username</label>
                    <i class="far fa-user"></i>
                    <input type="text" name="uname" placeholder="Admin username" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>

                <button type="submit" name="submit" class="btn-submit">Sign In to Portal</button>
            </form>
        </div>
    </div>

</body>
</html>