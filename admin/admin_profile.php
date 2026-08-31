<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include '../include/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../authentication/admin_login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$success_msg = "";
$error_msg = "";

// Fetch current admin details
$admin_query = mysqli_query($conn, "SELECT * FROM admin WHERE admin_id = $admin_id");
$admin = mysqli_fetch_assoc($admin_query);

// Automatically detect column names in your database table
$username_key = array_key_exists('username', $admin) ? 'username' : (array_key_exists('admin_name', $admin) ? 'admin_name' : 'name');
$email_key = array_key_exists('email', $admin) ? 'email' : 'admin_email';

// Handle Profile Update
if (isset($_POST['update_profile'])) {
    $username = isset($_POST['username']) ? mysqli_real_escape_string($conn, $_POST['username']) : '';
    $email = isset($_POST['email']) ? mysqli_real_escape_string($conn, $_POST['email']) : '';
    
    if (!empty($username) && !empty($email)) {
        $update_query = "UPDATE admin SET $username_key = '$username', $email_key = '$email' WHERE admin_id = $admin_id";
        if (mysqli_query($conn, $update_query)) {
            $success_msg = "Profile updated successfully!";
            // Refresh admin data array
            $admin_query = mysqli_query($conn, "SELECT * FROM admin WHERE admin_id = $admin_id");
            $admin = mysqli_fetch_assoc($admin_query);
        } else {
            $error_msg = "Error updating profile: " . mysqli_error($conn);
        }
    } else {
        $error_msg = "All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - IT Quiz Admin</title>
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

        .profile-card { background: white; border-radius: var(--radius-lg); border: 1px solid #e2e8f0; box-shadow: var(--shadow-sm); padding: 40px; max-width: 600px; box-sizing: border-box; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 14px; font-weight: 500; color: var(--text-dark); margin-bottom: 8px; }
        .form-control { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: var(--radius-md); font-size: 14px; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        .form-control:focus { outline: none; border-color: var(--accent-primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        
        .alert-success { background: #d1fae5; color: #065f46; padding: 12px; border-radius: var(--radius-md); margin-bottom: 20px; font-size: 14px; font-weight: 500; }
        .alert-error { background: #fee2e2; color: #b91c1c; padding: 12px; border-radius: var(--radius-md); margin-bottom: 20px; font-size: 14px; font-weight: 500; }
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
            <a href="manage_certificates.php" class="sidebar-link">Certificates</a>
            <a href="profile.php" class="sidebar-link active">Profile</a>
        </div>
        <a href="logout.php" class="sidebar-logout">Logout</a>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1>Admin Profile Settings</h1>
        </div>

        <div class="profile-card">
            <?php if (!empty($success_msg)): ?>
                <div class="alert-success"><?php echo $success_msg; ?></div>
            <?php endif; ?>
            <?php if (!empty($error_msg)): ?>
                <div class="alert-error"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Admin Username</label>
                    <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($admin[$username_key] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($admin[$email_key] ?? ''); ?>" required>
                </div>
                <button type="submit" name="update_profile" class="btn" style="width: 100%;">Save Changes</button>
            </form>
        </div>
    </div>

</body>
</html>