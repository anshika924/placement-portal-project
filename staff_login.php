<?php
require_once "config.php";
session_name("staff");
session_start();

if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: staff_access.php");
    exit;
}

$error = "";

if (isset($_POST['submit'])){
    $uname=$_POST['uname'];
    $password=$_POST['password'];
    $role=$_POST['role'];

    $uname=stripcslashes($uname);
    $password=stripcslashes($password);
    $uname=mysqli_real_escape_string($conn, $uname);
    $password=mysqli_real_escape_string($conn, $password);

    $query="select * from staff where staff_name='$uname' and password='$password' and role='$role'";
    $result=mysqli_query($conn,$query);

    if(mysqli_num_rows($result)==1 && $role=="admin"){
        $_SESSION["loggedin"] = true;
        $_SESSION["role"] = $role;
        $_SESSION["username"] = $uname; 
        header("location: staff_access.php");
        exit();
    } else {
        $error = "Wrong credentials or invalid role";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Placement Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #cce93a;
            --primary-dark: #b8d424;
            --emerald: #10b981; /* Exact color requested: #10b981 */
            --emerald-dark: #059669;
            --forest: #064e3b;
            --darker: #020617;
            --slate-500: #64748b;
            --slate-900: #0f172a;
            --white: #ffffff;
        }

        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; background: var(--white); height: 100vh; display: flex; overflow: hidden; }

        /* Left Side: Vibrant Animated Fluid Section (40%) */
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
        .blob-3 { top: 40%; left: 30%; width: 300px; height: 300px; background: #ffffff10; animation: fluidRotate 15s infinite alternate ease-in-out; }

        @keyframes fluidRotate {
            0% { transform: translate(0, 0) scale(1) rotate(0deg); }
            33% { transform: translate(120px, -50px) scale(1.1) rotate(60deg); }
            66% { transform: translate(-40px, 120px) scale(0.9) rotate(-40deg); }
            100% { transform: translate(80px, 40px) scale(1.05) rotate(20deg); }
        }

        .visual-footer { position: relative; z-index: 10; color: white; max-width: 400px; }
        .visual-footer h3 { font-size: 36px; font-weight: 800; line-height: 1.1; margin-bottom: 24px; text-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .visual-footer p { font-size: 16px; opacity: 0.9; line-height: 1.6; font-weight: 400; }

        /* Right Side: Auth Panel (60%) */
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
            color: var(--emerald); font-size: 26px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
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

        .auth-extras { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; font-size: 14px; width: 100%; }
        .remember-me { display: flex; align-items: center; gap: 10px; color: var(--slate-800); cursor: pointer; font-weight: 500; }
        .remember-me input { width: 18px; height: 18px; accent-color: var(--emerald); cursor: pointer; }
        .forgot-pass { color: var(--emerald); text-decoration: none; font-weight: 700; }

        /* EXACT Emerald Button (#10b981) */
        .btn-submit { 
            width: 100%; padding: 16px; border-radius: 12px; border: none; font-size: 16px; font-weight: 800; 
            cursor: pointer; transition: all 0.3s; color: white;
            background: var(--emerald);
            box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.4); margin-bottom: 24px;
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 15px 30px -5px rgba(16, 185, 129, 0.5); background: var(--emerald-dark); }

        .help-link { font-size: 14px; color: var(--slate-500); text-align: center; width: 100%; }
        .help-link a { color: var(--slate-900); font-weight: 700; text-decoration: none; border-bottom: 1.5px solid var(--slate-900); padding-bottom: 1px; }

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
        <div class="blob blob-3"></div>
        
        <div class="visual-footer">
            <h3>Infrastructure Control.</h3>
            <p>Coordinate across all placement administrative roles with real-time analytics and secure system access.</p>
        </div>
    </div>

    <div class="auth-side">
        <div class="auth-container">
            <div class="brand-icon"><i class="fas fa-microchip"></i></div>
            
            <div class="auth-header">
                <h1>Welcome back</h1>
                <p>Access the core administrative infrastructure tools.</p>
            </div>

            <?php if(!empty($error)): ?>
                <div class="error-msg"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
            <?php endif; ?>

            <form action="#" method="post">
                <div class="form-group">
                    <label>Access Role</label>
                    <i class="fas fa-user-shield"></i>
                    <input type="text" name="role" placeholder="e.g. admin" required>
                </div>

                <div class="form-group">
                    <label>Admin Username</label>
                    <i class="far fa-user"></i>
                    <input type="text" name="uname" placeholder="Enter your username" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>

                <div class="auth-extras">
                    <label class="remember-me">
                        <input type="checkbox"> Remember me
                    </label>
                    <a href="#" class="forgot-pass">Forgot password?</a>
                </div>

                <button type="submit" name="submit" class="btn-submit">Login Portal</button>
            </form>

            <div class="help-link">
                Trouble logging in? <a href="#">Need help?</a>
            </div>
        </div>
    </div>

</body>
</html>