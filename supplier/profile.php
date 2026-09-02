<?php
session_start();
include '../include/db.php';

// Prevent browser form resubmission warning
header("Cache-Control: private, must-revalidate, max-age=0");

if (!isset($_SESSION['supplier_id'])) {
    header("Location: ../authentication/supplier_login.php");
    exit();
}

$supplier_id = $_SESSION['supplier_id'];
$success_msg = "";
$error_msg = "";

// Handle Profile Update
if (isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    if (!empty($name) && !empty($email)) {
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "UPDATE supplier SET name = '$name', email = '$email', password = '$hashed_password' WHERE supplier_id = $supplier_id";
        } else {
            $query = "UPDATE supplier SET name = '$name', email = '$email' WHERE supplier_id = $supplier_id";
        }
        
        if (mysqli_query($conn, $query)) {
            $success_msg = "Profile updated successfully!";
        } else {
            $error_msg = "Error updating profile: " . mysqli_error($conn);
        }
    } else {
        $error_msg = "Name and Email are required fields.";
    }
}

// Fetch Supplier Details
$sup_query = mysqli_query($conn, "SELECT * FROM supplier WHERE supplier_id = $supplier_id");
$supplier_data = mysqli_fetch_assoc($sup_query);
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings - Instructor Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    <style>
        body { background-color: var(--bg-main); font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; display: flex; height: 100vh; overflow: hidden; }
        
        .sidebar { width: 280px; background: var(--surface-white); border-right: 1px solid var(--border-light); display: flex; flex-direction: column; padding: 25px 20px; box-sizing: border-box; height: 100vh; }
        .sidebar-brand { font-size: 18px; font-weight: 800; color: var(--text-primary); margin-bottom: 30px; display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .logo-icon { width: 32px; height: 32px; background: var(--brand-primary); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; }
        
        .sidebar-menu { display: flex; flex-direction: column; gap: 6px; flex: 1; }
        .sidebar-link { padding: 14px 16px; border-radius: var(--radius-md); text-decoration: none; color: var(--text-secondary); font-weight: 600; font-size: 14px; transition: var(--transition); border-left: 3px solid transparent; display: flex; align-items: center; gap: 10px; }
        .sidebar-link:hover, .sidebar-link.active { background: rgba(81, 70, 229, 0.08); color: var(--brand-primary); border-left-color: var(--brand-primary); }
        
        .sidebar-footer { padding-top: 20px; border-top: 1px solid var(--border-light); display: flex; align-items: center; justify-content: space-between; }
        .sidebar-logout { padding: 10px 16px; border-radius: 8px; text-decoration: none; color: var(--accent-coral); font-weight: 600; font-size: 13px; transition: var(--transition); background: rgba(255, 107, 74, 0.1); border: 1px solid rgba(255, 107, 74, 0.2); }
        .sidebar-logout:hover { background: var(--accent-coral); color: white; }

        .theme-toggle { background: none; border: 1px solid var(--border-light); width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-primary); transition: var(--transition); }
        .theme-toggle:hover { background: var(--border-light); }

        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; padding: 40px; box-sizing: border-box; background-color: var(--bg-main); }
        .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; }
        .dashboard-header h1 { font-size: 26px; font-weight: 800; color: var(--text-primary); margin: 0; letter-spacing: -0.5px; }

        .form-card { background: var(--surface-white); border-radius: var(--radius-lg); border: 1px solid var(--border-light); box-shadow: var(--shadow-subtle); padding: 40px; max-width: 600px; }
        .form-group { margin-bottom: 22px; }
        .form-group label { display: block; font-size: 13px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-control { width: 100%; padding: 14px 16px; border: 1.5px solid var(--border-light); background: var(--bg-main); color: var(--text-primary); border-radius: var(--radius-md); font-size: 15px; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; transition: var(--transition); }
        .form-control:focus { outline: none; border-color: var(--brand-primary); }
        
        .btn-custom { padding: 14px 24px; border-radius: var(--radius-md); font-weight: 600; font-size: 15px; text-decoration: none; cursor: pointer; transition: var(--transition); border: none; display: inline-block; width: 100%; text-align: center; }
        .btn-primary { background: var(--brand-primary); color: white; box-shadow: 0 4px 12px rgba(81, 70, 229, 0.2); }
        .btn-primary:hover { background: var(--brand-secondary); transform: translateY(-1px); }

        .alert { padding: 15px; border-radius: var(--radius-md); font-weight: 600; font-size: 14px; margin-bottom: 25px; }
        .alert-success { background: rgba(24, 183, 122, 0.1); border: 1px solid rgba(24, 183, 122, 0.2); color: var(--accent-green); }
        .alert-error { background: rgba(255, 107, 74, 0.1); border: 1px solid rgba(255, 107, 74, 0.2); color: var(--accent-coral); }
        .help-text { font-size: 12px; color: var(--text-secondary); margin-top: 6px; display: block; }
    </style>
</head>
<body>

    <div class="sidebar">
        <a href="../index.php" class="sidebar-brand">
            <div class="logo-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            </div>
            Instructor Portal
        </a>
        <div class="sidebar-menu">
            <a href="../index.php" class="sidebar-link">🏠 Home</a>
            <a href="dashboard.php" class="sidebar-link">📊 Dashboard</a>
            <a href="manage_question.php" class="sidebar-link">🧩 Manage Questions</a>
            <a href="profile.php" class="sidebar-link active">⚙️ Profile Settings</a>
        </div>
        <div class="sidebar-footer">
            <button class="theme-toggle" id="themeToggleBtn" aria-label="Toggle dark mode">
                <svg id="sunIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                <svg id="moonIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
            </button>
            <a href="logout.php" class="sidebar-logout">Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="dashboard-header">
            <h1>Profile Settings</h1>
        </div>

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-error"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <div class="form-card">
            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($supplier_data['name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($supplier_data['email'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter new password">
                    <span class="help-text">Leave blank if you do not want to change your password.</span>
                </div>
                <button type="submit" name="update_profile" class="btn-custom btn-primary" style="margin-top: 10px;">Save Profile Changes</button>
            </form>
        </div>
    </div>

    <script>
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const sunIcon = document.getElementById('sunIcon');
        const moonIcon = document.getElementById('moonIcon');
        const htmlElement = document.documentElement;

        const savedTheme = localStorage.getItem('theme') || 'light';
        htmlElement.setAttribute('data-theme', savedTheme);
        updateIcons(savedTheme);

        themeToggleBtn.addEventListener('click', () => {
            const currentTheme = htmlElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            htmlElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateIcons(newTheme);
        });

        function updateIcons(theme) {
            if (theme === 'dark') {
                sunIcon.style.display = 'block';
                moonIcon.style.display = 'none';
            } else {
                sunIcon.style.display = 'none';
                moonIcon.style.display = 'block';
            }
        }
    </script>
</body>
</html>