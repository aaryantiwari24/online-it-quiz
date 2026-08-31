<?php
session_start();
include '../include/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../authentication/admin_login.php");
    exit();
}

// Fetch all passing results (which act as verified certificates)
$query = "SELECT r.*, c.category_name, cu.name as customer_name 
          FROM result r 
          JOIN customer cu ON r.customer_id = cu.customer_id 
          LEFT JOIN category c ON r.category_id = c.category_id 
          WHERE r.status = 'Pass'
          ORDER BY r.attempt_date DESC";
$certificates = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Certificates - IT Quiz Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body { background-color: var(--bg-main); font-family: 'Poppins', sans-serif; margin: 0; display: flex; height: 100vh; overflow: hidden; }
        
        .sidebar { width: 260px; background: white; border-right: 1px solid #e2e8f0; display: flex; flex-direction: column; padding: 30px 20px; box-sizing: border-box; }
        .sidebar-brand { font-size: 20px; font-weight: 700; color: var(--text-dark); margin-bottom: 40px; }
        .sidebar-menu { display: flex; flex-direction: column; gap: 10px; flex: 1; }
        .sidebar-link { padding: 12px 16px; border-radius: var(--radius-md); text-decoration: none; color: var(--text-muted); font-weight: 500; font-size: 15px; transition: var(--transition); }
        .sidebar-link:hover, .sidebar-link.active { background: #eff6ff; color: var(--accent-primary); }
        .sidebar-logout { padding: 12px 16px; border-radius: var(--radius-md); text-decoration: none; color: #ef4444; font-weight: 500; font-size: 15px; transition: var(--transition); }
        .sidebar-logout:hover { background: #fee2e2; }

        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; padding: 40px; box-sizing: border-box; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; }
        .page-header h1 { font-size: 26px; color: var(--text-dark); margin: 0; }

        .table-card { background: white; border-radius: var(--radius-lg); border: 1px solid #e2e8f0; box-shadow: var(--shadow-sm); padding: 30px; }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 15px; }
        th { padding: 12px 16px; background: #f8fafc; color: var(--text-muted); font-weight: 600; border-bottom: 2px solid #e2e8f0; }
        td { padding: 14px 16px; border-bottom: 1px solid #e2e8f0; color: var(--text-dark); }
        tr:hover td { background: #f8fafc; }
        .btn-sm { padding: 6px 12px; border-radius: 6px; font-size: 13px; font-weight: 600; text-decoration: none; background: var(--accent-primary); color: white; display: inline-block; transition: var(--transition); }
        .btn-sm:hover { opacity: 0.9; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-brand">IT Quiz Admin</div>
        <div class="sidebar-menu">
            <a href="dashboard.php" class="sidebar-link">Dashboard</a>
            <a href="manage_customers.php" class="sidebar-link">Customers</a>
            <a href="manage_suppliers.php" class="sidebar-link">Suppliers</a>
            <a href="manage_category.php" class="sidebar-link">Categories</a>
            <a href="view_results.php" class="sidebar-link">Results</a>
            <a href="manage_certificates.php" class="sidebar-link active">Certificates</a>
            <a href="admin_profile.php" class="sidebar-link">Profile</a>
        </div>
        <a href="logout.php" class="sidebar-logout">Logout</a>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1>Issued Certificates & Verification</h1>
        </div>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Cert ID</th>
                        <th>Student Name</th>
                        <th>Category</th>
                        <th>Difficulty</th>
                        <th>Score</th>
                        <th>Date Earned</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($certificates) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($certificates)): ?>
                            <tr>
                                <td>#CERT-<?php echo $row['result_id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['customer_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['category_name'] ?? 'General'); ?></td>
                                <td><?php echo htmlspecialchars($row['difficulty'] ?? 'Standard'); ?></td>
                                <td><strong><?php echo $row['percentage']; ?>%</strong></td>
                                <td style="color: var(--text-muted); font-size: 13px;"><?php echo $row['attempt_date']; ?></td>
                                <td>
                                    <a href="../customer/certificate.php?result_id=<?php echo $row['result_id']; ?>" target="_blank" class="btn-sm">View Certificate</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 30px;">No certificates issued yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>