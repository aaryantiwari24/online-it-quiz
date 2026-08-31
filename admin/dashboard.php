<?php
session_start();
include '../include/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../authentication/admin_login.php");
    exit();
}

// Fetch metrics
$total_customers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM customer"))['count'];
$total_categories = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM category"))['count'];
$total_exams = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM result"))['count'];

// Fetch recent activity / test results
$recent_results_query = "SELECT r.*, c.category_name, cu.name as customer_name 
                         FROM result r 
                         JOIN customer cu ON r.customer_id = cu.customer_id 
                         LEFT JOIN category c ON r.category_id = c.category_id 
                         ORDER BY r.attempt_date DESC LIMIT 10";
$recent_results = mysqli_query($conn, $recent_results_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - IT Quiz</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body { background-color: var(--bg-main); font-family: 'Poppins', sans-serif; margin: 0; display: flex; height: 100vh; overflow: hidden; }
        
        /* Sidebar */
        .sidebar { width: 260px; background: white; border-right: 1px solid #e2e8f0; display: flex; flex-direction: column; padding: 30px 20px; box-sizing: border-box; }
        .sidebar-brand { font-size: 20px; font-weight: 700; color: var(--text-dark); margin-bottom: 40px; letter-spacing: 0.5px; }
        .sidebar-menu { display: flex; flex-direction: column; gap: 10px; flex: 1; overflow-y: auto; }
        .sidebar-link { padding: 12px 16px; border-radius: var(--radius-md); text-decoration: none; color: var(--text-muted); font-weight: 500; font-size: 15px; transition: var(--transition); }
        .sidebar-link:hover, .sidebar-link.active { background: #eff6ff; color: var(--accent-primary); }
        .sidebar-logout { padding: 12px 16px; border-radius: var(--radius-md); text-decoration: none; color: #ef4444; font-weight: 500; font-size: 15px; transition: var(--transition); }
        .sidebar-logout:hover { background: #fee2e2; }

        /* Main Content Area */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; padding: 40px; box-sizing: border-box; }
        .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; }
        .dashboard-header h1 { font-size: 26px; color: var(--text-dark); margin: 0; }
        .admin-badge { background: #eff6ff; color: var(--accent-primary); padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; }

        /* Metrics Grid */
        .metrics-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 40px; }
        .metric-card { background: white; padding: 25px; border-radius: var(--radius-lg); border: 1px solid #e2e8f0; box-shadow: var(--shadow-sm); }
        .metric-card h3 { margin: 0 0 10px 0; font-size: 13px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }
        .metric-card .number { font-size: 32px; font-weight: 700; color: var(--text-dark); margin: 0; }

        /* Data Table */
        .table-card { background: white; border-radius: var(--radius-lg); border: 1px solid #e2e8f0; box-shadow: var(--shadow-sm); padding: 30px; }
        .table-card h2 { margin-top: 0; font-size: 18px; color: var(--text-dark); margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 15px; }
        th { padding: 12px 16px; background: #f8fafc; color: var(--text-muted); font-weight: 600; border-bottom: 2px solid #e2e8f0; }
        td { padding: 14px 16px; border-bottom: 1px solid #e2e8f0; color: var(--text-dark); }
        tr:hover td { background: #f8fafc; }
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge.pass { background: #d1fae5; color: #065f46; }
        .badge.fail { background: #fee2e2; color: #b91c1c; }
    </style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="sidebar-brand">IT Quiz Admin</div>
        <div class="sidebar-menu">
            <a href="dashboard.php" class="sidebar-link active">Dashboard</a>
            <a href="manage_customers.php" class="sidebar-link">Customers</a>
            <a href="manage_suppliers.php" class="sidebar-link">Suppliers</a>
            <a href="manage_category.php" class="sidebar-link">Categories</a>
            <a href="view_results.php" class="sidebar-link">Results</a>
            <a href="manage_certificates.php" class="sidebar-link">Certificates</a>
            <a href="admin_profile.php" class="sidebar-link">Profile</a>
        </div>
        <a href="logout.php" class="sidebar-logout">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="dashboard-header">
            <h1>Platform Overview</h1>
            <div class="admin-badge">Administrator</div>
        </div>

        <!-- Metric Cards -->
        <div class="metrics-grid">
            <div class="metric-card">
                <h3>Total Customers</h3>
                <p class="number"><?php echo $total_customers; ?></p>
            </div>
            <div class="metric-card">
                <h3>Active Categories</h3>
                <p class="number"><?php echo $total_categories; ?></p>
            </div>
            <div class="metric-card">
                <h3>Total Exams Taken</h3>
                <p class="number"><?php echo $total_exams; ?></p>
            </div>
        </div>

        <!-- Recent Results Table -->
        <div class="table-card">
            <h2>Recent Certification Attempts</h2>
            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Category</th>
                        <th>Difficulty</th>
                        <th>Score</th>
                        <th>Status</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($recent_results) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($recent_results)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['category_name'] ?? 'General'); ?></td>
                                <td><?php echo htmlspecialchars($row['difficulty'] ?? 'Standard'); ?></td>
                                <td><strong><?php echo $row['percentage']; ?>%</strong></td>
                                <td>
                                    <span class="badge <?php echo strtolower($row['status']) === 'pass' ? 'pass' : 'fail'; ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <td style="color: var(--text-muted); font-size: 13px;"><?php echo $row['attempt_date']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">No certification attempts recorded yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>