<?php
session_start();
include '../include/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../authentication/admin_login.php");
    exit();
}

// Handle Add Category
if (isset($_POST['add_category'])) {
    $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);
    if (!empty($category_name)) {
        mysqli_query($conn, "INSERT INTO category (category_name) VALUES ('$category_name')");
        header("Location: manage_category.php");
        exit();
    }
}

// Handle Delete Category
if (isset($_GET['delete'])) {
    $category_id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM category WHERE category_id = $category_id");
    header("Location: manage_category.php");
    exit();
}

$categories = mysqli_query($conn, "SELECT * FROM category ORDER BY category_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - IT Quiz Admin</title>
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

        .content-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 30px; align-items: start; }
        
        .form-card, .table-card { background: white; border-radius: var(--radius-lg); border: 1px solid #e2e8f0; box-shadow: var(--shadow-sm); padding: 30px; }
        .form-card h2, .table-card h2 { margin-top: 0; font-size: 18px; color: var(--text-dark); margin-bottom: 20px; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 14px; font-weight: 500; color: var(--text-dark); margin-bottom: 8px; }
        .form-control { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: var(--radius-md); font-size: 14px; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        .form-control:focus { outline: none; border-color: var(--accent-primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }

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
            <a href="manage_suppliers.php" class="sidebar-link">Suppliers</a>
            <a href="manage_category.php" class="sidebar-link active">Categories</a>
            <a href="view_results.php" class="sidebar-link">Results</a>
            <a href="manage_certificates.php" class="sidebar-link">Certificates</a>
            <a href="admin_profile.php" class="sidebar-link">Profile</a>
        </div>
        <a href="logout.php" class="sidebar-logout">Logout</a>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1>Category Management</h1>
        </div>

        <div class="content-grid">
            <!-- Add Category Form -->
            <div class="form-card">
                <h2>Add New Category</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Category Name</label>
                        <input type="text" name="category_name" class="form-control" placeholder="e.g. Cybersecurity, React" required>
                    </div>
                    <button type="submit" name="add_category" class="btn" style="width: 100%;">Add Category</button>
                </form>
            </div>

            <!-- Categories Table -->
            <div class="table-card">
                <h2>Existing Categories</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Category Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($categories) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($categories)): ?>
                                <tr>
                                    <td><?php echo $row['category_id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['category_name']); ?></strong></td>
                                    <td>
                                        <a href="manage_category.php?delete=<?php echo $row['category_id']; ?>" class="btn-danger" onclick="return confirm('Are you sure you want to delete this category?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 30px;">No categories found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>