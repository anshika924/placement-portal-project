<?php  
require_once "config.php";
session_name("staff");
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: staff_login.php");
    exit;
}

$success_msg = "";
$error_msg = "";

if(isset($_POST["submit"]))
{
    if($_FILES['file']['name'])
    {
        $filename = explode(".", $_FILES['file']['name']);
        if($filename[1] == 'csv')
        {
            $handle = fopen($_FILES['file']['tmp_name'], "r");
            $not_found = 0;
            $updated = 0;

            while($data = fgetcsv($handle))
            {
                $item1 = mysqli_real_escape_string($conn, $data[0]);  
                $item2 = mysqli_real_escape_string($conn, $data[1]);
                $item3 = mysqli_real_escape_string($conn, $data[2]);

                $query1 = "SELECT * from marks where regdno='$item1'";
                $query = "UPDATE marks set cgpa='$item2', backlogs='$item3' WHERE regdno='$item1'";

                try {
                    $result = mysqli_query($conn, $query1);
                    if(mysqli_num_rows($result) == 1){
                        if(mysqli_query($conn, $query)) {
                            $updated++;
                        }
                    } else {
                        $not_found++;
                    }
                } catch(Exception $e) {
                    $not_found++;
                }
            }

            fclose($handle);
            if($not_found > 0) {
                $error_msg = "$not_found records were skipped (ID not found).";
            }
            if($updated > 0) {
                $success_msg = "$updated records successfully updated.";
            }
        } else {
            $error_msg = "Invalid file type. Please upload a CSV file.";
        }
    }
}
?>    

<!DOCTYPE html>  
<html lang="en">  
<head>  
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Database | Staff Portal</title>
    
    <link rel="stylesheet" href="dashboard_revamp.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        .upload-area {
            border: 2px dashed #e2e8f0;
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            background: #fbfcfd;
        }
        .upload-area:hover {
            border-color: var(--brand-emerald);
            background: #f0fdf4;
        }
        .upload-icon {
            font-size: 40px;
            color: var(--brand-emerald);
            margin-bottom: 16px;
        }
        .guide-box {
            background: #f8fafc;
            border-radius: 12px;
            padding: 24px;
            margin-top: 32px;
            border-left: 4px solid var(--brand-emerald);
        }
        .guide-box h3 {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--text-primary);
        }
        .guide-box ul {
            font-size: 13px;
            color: var(--text-secondary);
            padding-left: 20px;
        }
        .guide-box li { margin-bottom: 6px; }
        
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 600;
        }
        .alert-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background: #fffbeb; color: #92400e; border: 1px solid #fef3c7; }
        
        .submit-btn {
            width: 100%;
            padding: 14px;
            background: var(--brand-emerald);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 24px;
            transition: 0.3s;
        }
        .submit-btn:hover { background: #059669; transform: translateY(-2px); }
    </style>
</head>  

<body>

    <div class="dashboard-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i class="fas fa-shield-halved"></i>
                Admin Portal
            </div>

            <ul class="nav-links">
                <li class="nav-item"><a href="staff_access.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li class="nav-item"><a href="insertdata.php"><i class="fas fa-plus-circle"></i> Insert New Student</a></li>
                <li class="nav-item active"><a href="updatedata.php"><i class="fas fa-pen-to-square"></i> Update Records</a></li>
                <li class="nav-item"><a href="student_details.php"><i class="fas fa-user-group"></i> View Students</a></li>
                <li class="nav-item"><a href="generatefile.php"><i class="fas fa-file-chart-column"></i> Placement Reports</a></li>
                <li class="nav-item"><a href="staff_reset_password.php"><i class="fas fa-lock"></i> Security Settings</a></li>
            </ul>

            <div class="sidebar-footer">
                <a href="staff_logout.php" style="text-decoration: none;">
                    <div class="user-pill">
                        <img src="https://api.dicebear.com/7.x/shapes/svg?seed=Admin" alt="Admin Avatar">
                        <div style="flex: 1;">
                            <p style="font-size: 13px; font-weight: 700; color: var(--text-primary);">Admin Portal</p>
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
                    <h1 style="font-family: var(--font-heading); font-size: 24px; font-weight: 600;">System Synchronizer</h1>
                    <p style="color: var(--text-secondary); margin-top: 4px;">Update existing academic profiles via legacy CSV synchronization.</p>
                </div>
            </header>

            <div class="data-panel animate-up" style="max-width: 800px; margin: 0 auto;">
                <div class="data-header">
                    <h2>Batch Record Update</h2>
                </div>

                <?php if($success_msg): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success_msg; ?></div>
                <?php endif; ?>
                <?php if($error_msg): ?>
                    <div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> <?php echo $error_msg; ?></div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <label class="upload-area" for="file-input">
                        <i class="fas fa-arrows-rotate upload-icon"></i>
                        <p style="font-weight: 700; color: var(--text-primary); margin-bottom: 4px;">Select Update CSV</p>
                        <p style="font-size: 12px; color: var(--text-secondary);">Upload to overwrite CGPA and Backlogs for matched accounts</p>
                        <input type="file" name="file" id="file-input" required style="display: none;">
                        <p id="file-name" style="margin-top:10px; font-size:12px; color:var(--brand-emerald); font-weight:700;"></p>
                    </label>

                    <button type="submit" name="submit" class="submit-btn">
                        Apply System-wide Update
                    </button>
                </form>

                <div class="guide-box" style="border-left-color: var(--brand-emerald);">
                    <h3><i class="fas fa-info-circle"></i> Synchronization Format</h3>
                    <ul>
                        <li>Records are matched by <strong>Registration Number</strong>.</li>
                        <li><strong>CGPA</strong> and <strong>Backlogs</strong> will be updated if the student exists.</li>
                        <li>Invalid or non-existent IDs will be reported in the summary above.</li>
                    </ul>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.getElementById('file-input').onchange = function() {
            document.getElementById('file-name').innerHTML = "<i class='fas fa-file-csv'></i> Ready: " + this.files[0].name;
        };
    </script>

</body>  
</html>