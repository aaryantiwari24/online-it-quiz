<?php
session_start();
include '../include/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../authentication/admin_login.php");
    exit();
}

// Handle Delete Supplier
if (isset($_GET['delete'])) {
    $supplier_id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM supplier WHERE supplier_id = $supplier_id");
    header("Location: manage_suppliers.php");
    exit();
}

$suppliers = mysqli_query($conn, "SELECT * FROM supplier ORDER BY supplier_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Suppliers - IT Quiz Admin</title>
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
        .btn-danger { background: #fee2e2; color: #dc2626; padding: 6px 12px; border-radius: 6px; font-size: 13px; font-weight: 600; text-decoration: none; transition: var(--transition); }
        .btn-danger:hover { background: #fca5a5; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-brand">IT Quiz Admin</div>
        <div class="sidebar-menu">
            <a href="dashboard.php" class="sidebar-link">Dashboard</a>
            <a href="manage_customers.php" class="sidebar-link">Customers</a>
            <a href="manage_suppliers.php" class="sidebar-link active">Suppliers</a>
            <a href="manage_category.php" class="sidebar-link">Categories</a>
            <a href="view_results.php" class="sidebar-link">Results</a>
            <a href="manage_certificates.php" class="sidebar-link">Certificates</a>
            <a href="admin_profile.php" class="sidebar-link">Profile</a>
        </div>
        <a href="logout.php" class="sidebar-logout">Logout</a>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1>Supplier Management</h1>
        </div>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($suppliers) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($suppliers)): ?>
                            <tr>
                                <td><?php echo $row['supplier_id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                <td style="color: var(--text-muted);"><?php echo htmlspecialchars($row['email']); ?></td>
                                <td>
                                    <a href="manage_suppliers.php?delete=<?php echo $row['supplier_id']; ?>" class="btn-danger" onclick="return confirm('Are you sure you want to delete this supplier?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 30px;">No registered suppliers found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>