<?php
session_start();
include '../include/db.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../authentication/customer_login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$success_msg = "";
$error_msg = "";

// Handle Profile Update
if (isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    if (!empty($name) && !empty($email)) {
        // Update password only if a new one is typed
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_query = "UPDATE customer SET name = '$name', email = '$email', password = '$hashed_password' WHERE customer_id = $customer_id";
        } else {
            $update_query = "UPDATE customer SET name = '$name', email = '$email' WHERE customer_id = $customer_id";
        }

        if (mysqli_query($conn, $update_query)) {
            $success_msg = "Profile updated successfully!";
            // Update session name if it changed
            $_SESSION['customer_name'] = $name;
        } else {
            $error_msg = "Error updating profile: " . mysqli_error($conn);
        }
    } else {
        $error_msg = "Name and Email are required.";
    }
}

// Fetch current customer details
$customer_query = mysqli_query($conn, "SELECT * FROM customer WHERE customer_id = $customer_id");
$customer = mysqli_fetch_assoc($customer_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile - IT Quiz</title>
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
        .help-text { font-size: 12px; color: var(--text-muted); margin-top: 5px; display: block; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-brand">IT Quiz Platform</div>
        <div class="sidebar-menu">
            <a href="dashboard.php" class="sidebar-link">Dashboard</a>
            <a href="certificate.php" class="sidebar-link">My Certificates</a>
            <a href="profile.php" class="sidebar-link active">Profile</a>
        </div>
        <a href="logout.php" class="sidebar-logout">Logout</a>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1>My Profile</h1>
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
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($customer['name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($customer['email'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter new password">
                    <span class="help-text">Leave blank if you do not want to change your password.</span>
                </div>
                <button type="submit" name="update_profile" class="btn" style="width: 100%;">Save Changes</button>
            </form>
        </div>
    </div>

</body>
</html>