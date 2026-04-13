<?php 
require_once 'config.php';
session_start();

// Ensure student is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["num"])){
    header("location: student_login.php");
    exit;
}

$regdno = $_SESSION["num"];
$message = "";
$message_type = "";

if (isset($_POST["submit"])) {
    $offer_id = mysqli_real_escape_string($conn, $_POST["id"]);
    $company = mysqli_real_escape_string($conn, $_POST["title"]);
    $package = mysqli_real_escape_string($conn, $_POST["pac"]);
    
    // File handling
    $original_name = $_FILES["file"]["name"];
    $pname = rand(1000, 9999) . "-" . preg_replace("/[^a-zA-Z0-9.]/", "_", $original_name);
    $tname = $_FILES["file"]["tmp_name"];
    $upload_dir = "uploaded_files/";

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (move_uploaded_file($tname, $upload_dir . $pname)) {
        // Use IGNORE or check existence to prevent duplicates if necessary
        $sql = "INSERT INTO package (regdno, companyname, package, file) VALUES ('$regdno', '$company', '$package', '$pname')";
        
        try {
            if (mysqli_query($conn, $sql)) {
                $message = "Your offer letter for $company has been successfully uploaded and is pending verification.";
                $message_type = "success";
            } else {
                $message = "Database error: Could not save the record.";
                $message_type = "error";
            }
        } catch (Exception $e) {
            $message = "You have already uploaded an offer for this company.";
            $message_type = "error";
        }
    } else {
        $message = "File system error: Could not move the uploaded file. Check folder permissions.";
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Status | Starline</title>
    
    <link rel="stylesheet" href="dashboard_revamp.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        .status-card {
            max-width: 500px;
            margin: 100px auto;
            text-align: center;
            padding: 48px;
        }
        .icon-box {
            font-size: 64px;
            margin-bottom: 24px;
        }
        .success { color: var(--brand-emerald); }
        .error { color: #ef4444; }
        .back-btn {
            display: inline-block;
            margin-top: 32px;
            padding: 12px 32px;
            background: var(--brand-emerald);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            text-transform: uppercase;
            transition: 0.3s;
        }
        .back-btn:hover {
            background: #059669;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

    <div class="status-card data-panel animate-up">
        <div class="icon-box <?php echo $message_type; ?>">
            <i class="fas <?php echo $message_type == 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i>
        </div>
        
        <h2 style="font-family: var(--font-heading); font-size: 24px; font-weight: 600; margin-bottom: 12px;">
            <?php echo $message_type == 'success' ? 'Upload Confirmed' : 'Inbound Failure'; ?>
        </h2>
        
        <p style="color: var(--text-secondary); line-height: 1.6;">
            <?php echo $message; ?>
        </p>

        <a href="student_profile.php" class="back-btn">Return to Profile</a>
    </div>

</body>
</html>
