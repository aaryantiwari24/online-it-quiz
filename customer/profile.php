<?php
session_start();
include '../include/db.php';

// Prevent browser form resubmission warning
header("Cache-Control: private, must-revalidate, max-age=0");

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../authentication/customer_login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$success_msg = "";
$error_msg = "";

// Handle Profile Update Request
if (isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($name) && !empty($email)) {
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_query = "UPDATE customer SET name = ?, email = ?, password = ? WHERE customer_id = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $hashed_password, $customer_id);
        } else {
            $update_query = "UPDATE customer SET name = ?, email = ? WHERE customer_id = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "ssi", $name, $email, $customer_id);
        }

        if (mysqli_stmt_execute($stmt)) {
            $success_msg = "Profile updated successfully!";
            $_SESSION['customer_name'] = $name;
        } else {
            $error_msg = "Error updating profile: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_msg = "Name and Email are required.";
    }
}

// Fetch Latest User Details
$user_query = "SELECT * FROM customer WHERE customer_id = $customer_id";
$user_result = mysqli_query($conn, $user_query);
$user_data = mysqli_fetch_assoc($user_result);
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings - Student Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>

    <style>
        body { display: flex; height: 100vh; overflow: hidden; background-color: var(--bg-main); font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; }
        
        /* Sidebar Layout */
        .sidebar { width: 280px; background: var(--surface-white); border-right: 1px solid var(--border-light); display: flex; flex-direction: column; height: 100vh; transition: background 0.3s ease, border 0.3s ease; padding: 25px 20px; box-sizing: border-box; }
        .sidebar-header { font-size: 18px; font-weight: 800; color: var(--text-primary); margin-bottom: 30px; display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .logo-icon { width: 32px; height: 32px; background: var(--brand-primary); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; }
        
        .sidebar-menu { flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 6px; }
        .menu-item { padding: 14px 16px; color: var(--text-secondary); text-decoration: none; font-size: 14px; font-weight: 600; transition: var(--transition); border-left: 3px solid transparent; display: flex; align-items: center; gap: 10px; cursor: pointer; border-radius: var(--radius-md); }
        .menu-item:hover, .menu-item.active { background: rgba(81, 70, 229, 0.08); color: var(--brand-primary); border-left-color: var(--brand-primary); }
        
        .sidebar-footer { padding-top: 20px; border-top: 1px solid var(--border-light); display: flex; align-items: center; justify-content: space-between; }
        .btn-logout { display: inline-block; text-align: center; background: rgba(255, 107, 74, 0.1); color: var(--accent-coral); text-decoration: none; padding: 10px 16px; border-radius: 8px; font-weight: 600; font-size: 13px; transition: var(--transition); border: 1px solid rgba(255, 107, 74, 0.2); }
        .btn-logout:hover { background: var(--accent-coral); color: white; }

        .theme-toggle { background: none; border: 1px solid var(--border-light); width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-primary); transition: var(--transition); }
        .theme-toggle:hover { background: var(--border-light); }

        /* Main Workspace */
        .main-content { flex: 1; overflow-y: auto; padding: 40px; background-color: var(--bg-main); }
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; }
        .header-top h2 { font-size: 26px; font-weight: 800; color: var(--text-primary); letter-spacing: -0.5px; margin: 0; }
        .user-badge { background: var(--surface-white); padding: 10px 18px; border-radius: 30px; border: 1px solid var(--border-light); font-size: 14px; font-weight: 600; color: var(--text-primary); box-shadow: var(--shadow-subtle); display: flex; align-items: center; gap: 8px; }

        .premium-card { background: var(--surface-white); padding: 40px; border-radius: var(--radius-lg); border: 1px solid var(--border-light); box-shadow: var(--shadow-subtle); max-width: 600px; }
        .form-group { margin-bottom: 22px; }
        .form-group label { display: block; font-size: 13px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-control { width: 100%; padding: 14px 16px; border: 1.5px solid var(--border-light); background: var(--bg-main); color: var(--text-primary); border-radius: var(--radius-md); font-size: 15px; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; transition: var(--transition); }
        .form-control:focus { outline: none; border-color: var(--brand-primary); }
        
        .btn-action { display: inline-block; padding: 14px 24px; background: var(--brand-primary); color: white; text-decoration: none; border-radius: var(--radius-md); font-weight: 600; font-size: 15px; transition: var(--transition); text-align: center; border: none; cursor: pointer; width: 100%; }
        .btn-action:hover { background: var(--brand-secondary); transform: translateY(-1px); }

        .alert-success { background: rgba(24, 183, 122, 0.1); color: var(--accent-green); padding: 12px; border-radius: var(--radius-md); margin-bottom: 20px; font-size: 14px; font-weight: 600; border: 1px solid rgba(24, 183, 122, 0.2); }
        .alert-error { background: rgba(255, 107, 74, 0.1); color: var(--accent-coral); padding: 12px; border-radius: var(--radius-md); margin-bottom: 20px; font-size: 14px; font-weight: 600; border: 1px solid rgba(255, 107, 74, 0.2); }
        .help-text { font-size: 12px; color: var(--text-secondary); margin-top: 6px; display: block; }
    </style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <a href="../index.php" class="sidebar-header">
            <div class="logo-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            </div>
            IT Quiz Student
        </a>
        
        <div class="sidebar-menu">
            <a href="../index.php" class="menu-item">🏠 Home</a>
            <a href="dashboard.php" class="menu-item">📊 Dashboard Overview</a>
            <a href="dashboard.php#quizzes" class="menu-item">🧩 Available Quizzes</a>
            <a href="dashboard.php#history" class="menu-item">📜 Evaluation History</a>
            <a href="profile.php" class="menu-item active">⚙️ My Profile</a>
        </div>
        
        <div class="sidebar-footer">
            <button class="theme-toggle" id="themeToggleBtn" aria-label="Toggle dark mode">
                <svg id="sunIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                <svg id="moonIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
            </button>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </div>

    <!-- Main Content Panel -->
    <div class="main-content">
        <div class="header-top">
            <h2>Profile Settings</h2>
            <div class="user-badge">👤 <?php echo htmlspecialchars($user_data['name'] ?? 'Student'); ?></div>
        </div>

        <div class="premium-card">
            <?php if (!empty($success_msg)): ?>
                <div class="alert-success"><?php echo $success_msg; ?></div>
            <?php endif; ?>
            <?php if (!empty($error_msg)): ?>
                <div class="alert-error"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user_data['name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter new password">
                    <span class="help-text">Leave blank if you do not want to change your password.</span>
                </div>
                <button type="submit" name="update_profile" class="btn-action" style="margin-top: 10px;">Save Changes</button>
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