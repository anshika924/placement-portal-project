<?php
session_name("hod");
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: hod_login.php");
    exit;
}

$all_categories = null;
if (isset($conn) && $conn instanceof mysqli) {
    try {
        $sql = "SELECT * FROM company";
        $all_categories = mysqli_query($conn, $sql);
    } catch (Throwable $e) { }
}

// Logic for search status
if(isset($_POST['submit']) && isset($_POST['regdno']))
{
    $regdno = mysqli_real_escape_string($conn, $_POST['regdno']);    
    $_SESSION['r'] = $regdno;
    header("location: hod_studentDetails.php");
    exit;
}

// Fetch HOD stats
$total_placed = 0;
if (isset($conn) && $conn instanceof mysqli) {
    try {
        // Correctly count uniquely placed students
        $total_placed_res = $conn->query("SELECT COUNT(DISTINCT regdno) as count FROM package");
        if ($total_placed_res) $total_placed = $total_placed_res->fetch_assoc()['count'];
    } catch (Throwable $e) { }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOD Insights | Starline Placement</title>
    
    <link rel="stylesheet" href="dashboard_revamp.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        .form-grid { display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 20px; align-items: end; }
        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; font-size: 13px; font-weight: 700; color: var(--text-secondary); margin-bottom: 8px; text-transform: uppercase; }
        .input-group input, .input-group select {
            width: 100%; padding: 12px 14px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 14px; font-family: inherit; color: var(--text-primary);
        }
        .input-group input:focus { outline: none; border-color: var(--brand-emerald); box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1); }
        .submit-btn {
            width: 100%; padding: 13px; background: var(--brand-emerald); color: white; border: none; border-radius: 10px; font-weight: 800; font-size: 13px; cursor: pointer; text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.3s;
        }
        .submit-btn:hover { background: #059669; transform: translateY(-2px); }
        
        .widget-value.loading { color: transparent; background: #f1f5f9; border-radius: 6px; animation: pulse 1.5s infinite; }
        @keyframes pulse { 0% { opacity: 0.6; } 50% { opacity: 1; } 100% { opacity: 0.6; } }
    </style>
</head>
<body>

    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-logo"><i class="fas fa-chart-line-up"></i> HOD Portal</div>

            <ul class="nav-links">
                <li class="nav-item active"><a href="#"><i class="fas fa-magnifying-glass-chart"></i> Strategic Overview</a></li>
                <li class="nav-item"><a href="hodcompanylist.php"><i class="fas fa-users-viewfinder"></i> Eligibility Filter</a></li>
                <li class="nav-item"><a href="hod_studentDetails.php"><i class="fas fa-file-invoice-dollar"></i> Placement Status</a></li>
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

        <main class="main-content">
            <header class="header animate-up">
                <div>
                    <h1 style="font-family: var(--font-heading); font-size: 24px; font-weight: 600;">Strategic Insights</h1>
                    <p style="color: var(--text-secondary); margin-top: 4px;">Departmental intelligence and recruitment analysis.</p>
                </div>
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Filter talent database...">
                </div>
            </header>

            <div class="widget-grid animate-up" style="animation-delay: 0.1s;">
                <div class="widget-card">
                    <div class="widget-icon"><i class="fas fa-user-check"></i></div>
                    <div class="widget-value loading" id="placed-count"><?php echo (int)$total_placed; ?></div>
                    <div class="widget-label">Students Placed</div>
                </div>
                <div class="widget-card">
                    <div class="widget-icon"><i class="fas fa-award"></i></div>
                    <div class="widget-value">24.5 LPA</div>
                    <div class="widget-label">Avg. Dream Offer</div>
                </div>
                <div class="widget-card">
                    <div class="widget-icon"><i class="fas fa-check-double"></i></div>
                    <div class="widget-value">92%</div>
                    <div class="widget-label">Success Analytics</div>
                </div>
                <div class="widget-card">
                    <div class="widget-icon"><i class="fas fa-clock-rotate-left"></i></div>
                    <div class="widget-value">Active</div>
                    <div class="widget-label">Recruitment Phase</div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1.8fr 1.2fr; gap: 32px;" class="animate-up" style="animation-delay: 0.2s;">
                <div class="data-panel">
                    <div class="data-header">
                        <h2>Talent Eligibility Filter</h2>
                        <span style="font-size: 11px; font-weight: 800; color: var(--brand-emerald); background: #f0fdf4; padding: 4px 10px; border-radius: 6px;">Live Data Engine</span>
                    </div>

                    <form method="POST" action="hodcompanylist.php">
                        <div class="form-grid">
                            <div class="input-group">
                                <label>Target Enterprise</label>
                                <select name="company" required>
                                    <option value="" disabled selected>Select Company...</option>
                                    <?php if($all_categories) while ($category = mysqli_fetch_array($all_categories, MYSQLI_ASSOC)): ?>
                                        <option value="<?php echo htmlspecialchars($category["companyname"]); ?>">
                                            <?php echo htmlspecialchars(ucfirst($category["companyname"])); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="input-group">
                                <label>Min. CGPA</label>
                                <input type="number" name="cgpa" step="0.01" min="4" max="10" placeholder="0.00" required>
                            </div>
                            <div class="input-group">
                                <label>Max Backlogs</label>
                                <input type="number" name="backlogs" min="0" max="5" placeholder="0" required>
                            </div>
                        </div>
                        <button type="submit" name="submit" class="submit-btn" style="width: auto; padding: 13px 40px; float: right; margin-top: 0;">Apply Filters</button>
                    </form>
                </div>

                <div class="data-panel">
                    <div class="data-header"><h2>Quick Verification</h2></div>
                    <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 24px; line-height: 1.6;">
                        Enter a registration number to instantly access individual placement status and offer letters.
                    </p>
                    <form method="POST" action="">
                        <div class="input-group">
                            <label>Registration Number</label>
                            <input type="text" name="regdno" placeholder="e.g. 21CSE101" required>
                        </div>
                        <button type="submit" name="submit" class="submit-btn" style="background: transparent; border: 1px solid #e2e8f0; color: var(--text-primary);">Lookup Professional Status</button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/countup.js/1.9.3/countUp.min.js"></script>
    <script>
        function initCounters() {
            if (typeof CountUp === 'undefined') {
                setTimeout(initCounters, 100);
                return;
            }
            const placedCountEl = document.getElementById('placed-count');
            const totalPlaced = parseInt(placedCountEl.innerText) || 0;
            placedCountEl.classList.remove('loading');
            new CountUp('placed-count', 0, totalPlaced, 0, 2).start();
        }
        window.onload = initCounters;
    </script>
</body>
</html>