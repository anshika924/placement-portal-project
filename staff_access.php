<?php
session_name("staff");
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: staff_login.php");
    exit;
}
require_once 'config.php';

// Fetch basic stats for the widgets
$total_students = 0;
$total_companies = 0;
try {
    $total_students_res = $conn->query("SELECT COUNT(*) as count FROM student");
    if ($total_students_res) $total_students = $total_students_res->fetch_assoc()['count'];

    $total_companies_res = $conn->query("SELECT COUNT(*) as count FROM company");
    if ($total_companies_res) $total_companies = $total_companies_res->fetch_assoc()['count'];

    // New: Count uniquely placed students
    $placed_students_res = $conn->query("SELECT COUNT(DISTINCT regdno) as count FROM package");
    if ($placed_students_res) $placed_students = $placed_students_res->fetch_assoc()['count'];
} catch (Exception $e) { }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Starline Placement</title>
    
    <link rel="stylesheet" href="dashboard_revamp.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        .input-group {
            margin-bottom: 20px;
        }
        .input-group label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: var(--text-secondary);
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .input-group input, .input-group select {
            width: 100%;
            padding: 12px 16px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            color: var(--text-primary);
            transition: all 0.2s;
        }
        .input-group input:focus {
            outline: none;
            border-color: var(--brand-emerald);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }
        .submit-btn {
            width: 100%;
            padding: 14px;
            background: var(--brand-emerald);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 800;
            font-size: 14px;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            margin-top: 20px;
        }
        .submit-btn:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.2);
        }
        
        /* Skeleton loading */
        .widget-value.loading { color: transparent; background: #f1f5f9; border-radius: 6px; animation: pulse 1.5s infinite; }
        @keyframes pulse { 0% { opacity: 0.6; } 50% { opacity: 1; } 100% { opacity: 0.6; } }
    </style>
</head>
<body>

    <div class="dashboard-wrapper">
        <!-- Sidebar Navigation (Consolidated Actions) -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i class="fas fa-shield-halved"></i>
                Admin Portal
            </div>

            <ul class="nav-links">
                <li class="nav-item active"><a href="staff_access.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li class="nav-item"><a href="insertdata.php"><i class="fas fa-plus-circle"></i> Insert New Student</a></li>
                <li class="nav-item"><a href="updatedata.php"><i class="fas fa-pen-to-square"></i> Update Records</a></li>
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

        <!-- Main Content -->
        <main class="main-content">
            <header class="header animate-up">
                <div>
                    <h1 style="font-family: var(--font-heading); font-size: 24px; font-weight: 600;">Operational Hub</h1>
                    <p style="color: var(--text-secondary); margin-top: 4px;">System management and recruitment infrastructure.</p>
                </div>
                
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search system resources...">
                </div>
            </header>

            <!-- Stats Widgets -->
            <div class="widget-grid animate-up" style="animation-delay: 0.1s;">
                <div class="widget-card">
                    <div class="widget-icon"><i class="fas fa-users"></i></div>
                    <div class="widget-value loading" id="student-count"><?php echo (int)$total_students; ?></div>
                    <div class="widget-label">Managed Students</div>
                </div>

                <div class="widget-card">
                    <div class="widget-icon"><i class="fas fa-building-columns"></i></div>
                    <div class="widget-value loading" id="company-count"><?php echo (int)$total_companies; ?></div>
                    <div class="widget-label">Partner Companies</div>
                </div>

                <div class="widget-card">
                    <div class="widget-icon"><i class="fas fa-graduation-cap"></i></div>
                    <div class="widget-value loading" id="placed-count"><?php echo (int)$placed_students; ?></div>
                    <div class="widget-label">Placed Students</div>
                </div>

                <div class="widget-card">
                    <div class="widget-icon"><i class="fas fa-calendar-check"></i></div>
                    <div class="widget-value">Active</div>
                    <div class="widget-label">Drive Season</div>
                </div>
            </div>

            <!-- Consolidated Update Form (Minimalist Panel) -->
            <div class="data-panel animate-up" style="animation-delay: 0.2s;">
                <div class="data-header">
                    <h2>Update Placement Status</h2>
                    <span style="font-size: 12px; font-weight: 600; color: var(--text-secondary); background: #f1f5f9; padding: 4px 10px; border-radius: 6px;">
                        <i class="fas fa-info-circle"></i> Direct Update Mode
                    </span>
                </div>

                <div class="form-container">
                    <form action="update_placement.php" method="POST">
                        <div class="form-grid">
                            <div class="input-group">
                                <label>Student Registration Number</label>
                                <input type="text" name="regdno" placeholder="e.g. 21CSE101" required>
                            </div>
                            <div class="input-group">
                                <label>Company Name</label>
                                <input type="text" name="company" placeholder="e.g. Microsoft" required>
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="input-group">
                                <label>Package (LPA)</label>
                                <input type="number" name="package" step="0.1" placeholder="e.g. 12.5" required>
                            </div>
                            <div class="input-group">
                                <label>Work Location</label>
                                <input type="text" name="location" placeholder="e.g. Bangalore" required>
                            </div>
                        </div>

                        <button type="submit" name="submit" class="submit-btn" style="font-weight: 600;">
                            Confirm Placement Record
                        </button>
                    </form>
                </div>
            </div>
            
        </main>
    </div>

    <!-- Counters logic -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/countup.js/1.9.3/countUp.min.js"></script>
    <script>
        function initCounters() {
            if (typeof CountUp === 'undefined') {
                setTimeout(initCounters, 100);
                return;
            }
            const studentCountEl = document.getElementById('student-count');
            const companyCountEl = document.getElementById('company-count');
            const placedCountEl = document.getElementById('placed-count');
            const totalStudents = parseInt(studentCountEl.innerText) || 0;
            const totalCompanies = parseInt(companyCountEl.innerText) || 0;
            const totalPlaced = parseInt(placedCountEl.innerText) || 0;

            studentCountEl.classList.remove('loading');
            companyCountEl.classList.remove('loading');
            placedCountEl.classList.remove('loading');
            new CountUp('student-count', 0, totalStudents, 0, 2).start();
            new CountUp('company-count', 0, totalCompanies, 0, 2).start();
            new CountUp('placed-count', 0, totalPlaced, 0, 2).start();
        }
        window.onload = initCounters;
    </script>

</body>
</html>