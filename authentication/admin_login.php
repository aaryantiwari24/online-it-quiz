<?php
session_start();
include '../include/db.php';

$error = "";

if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $stmt = mysqli_prepare($conn, "SELECT admin_id, email, password FROM admin WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) == 1) {
            $admin = mysqli_fetch_assoc($result);
            
            if (password_verify($password, $admin['password'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_email'] = $admin['email'];
                header("Location: ../admin/dashboard.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $error = "All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - IT Quiz</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    <style>
        body { display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background-color: var(--bg-main); font-family: 'Plus Jakarta Sans', sans-serif; }
        .auth-card { background: var(--surface-white); padding: 40px; border-radius: var(--radius-lg); border: 1px solid var(--border-light); box-shadow: var(--shadow-subtle); width: 100%; max-width: 420px; box-sizing: border-box; }
        .auth-card h2 { margin-top: 0; font-size: 24px; font-weight: 800; color: var(--text-primary); margin-bottom: 8px; text-align: center; }
        .auth-subtitle { font-size: 14px; color: var(--text-secondary); text-align: center; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: var(--text-primary); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-control { width: 100%; padding: 14px 16px; border: 1.5px solid var(--border-light); background: var(--bg-main); color: var(--text-primary); border-radius: var(--radius-md); font-size: 15px; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; transition: var(--transition); }
        .form-control:focus { outline: none; border-color: var(--brand-primary); box-shadow: 0 0 0 4px rgba(81, 70, 229, 0.1); }
        .alert-error { background: rgba(255, 107, 74, 0.1); color: var(--accent-coral); padding: 12px; border-radius: var(--radius-md); margin-bottom: 20px; font-size: 14px; font-weight: 600; text-align: center; border: 1px solid rgba(255, 107, 74, 0.2); }
        .btn-submit { width: 100%; background: var(--brand-primary); color: white; padding: 14px; border-radius: var(--radius-md); font-weight: 700; font-size: 15px; border: none; cursor: pointer; transition: var(--transition); box-shadow: 0 4px 12px rgba(81, 70, 229, 0.2); }
        .btn-submit:hover { background: var(--brand-secondary); transform: translateY(-2px); }
        .auth-footer { text-align: center; margin-top: 25px; font-size: 14px; color: var(--text-secondary); }
        .auth-footer a { color: var(--brand-primary); text-decoration: none; font-weight: 600; }
        .auth-footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <div class="auth-card">
        <h2>Admin Console</h2>
        <div class="auth-subtitle">Secure workspace authentication</div>
        
        <?php if (!empty($error)): ?>
            <div class="alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" name="login" class="btn-submit">Authenticate</button>
        </form>

        <div class="auth-footer">
            <a href="../index.php">← Back to Homepage</a>
        </div>
    </div>

</body>
</html>