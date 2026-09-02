<?php
session_start();
include '../include/db.php';

$error = "";
$success = "";

if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    if (!empty($name) && !empty($email) && !empty($password)) {
        $check_query = mysqli_query($conn, "SELECT * FROM supplier WHERE email = '$email'");
        if (mysqli_num_rows($check_query) > 0) {
            $error = "Email is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_query = "INSERT INTO supplier (name, email, password) VALUES ('$name', '$email', '$hashed_password')";
            
            if (mysqli_query($conn, $insert_query)) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Registration failed: " . mysqli_error($conn);
            }
        }
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
    <title>Supplier Registration - IT Quiz</title>
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
        .alert-success { background: rgba(24, 183, 122, 0.1); color: var(--accent-green); padding: 12px; border-radius: var(--radius-md); margin-bottom: 20px; font-size: 14px; font-weight: 600; text-align: center; border: 1px solid rgba(24, 183, 122, 0.2); }
        .btn-submit { width: 100%; background: var(--brand-primary); color: white; padding: 14px; border-radius: var(--radius-md); font-weight: 700; font-size: 15px; border: none; cursor: pointer; transition: var(--transition); box-shadow: 0 4px 12px rgba(81, 70, 229, 0.2); }
        .btn-submit:hover { background: var(--brand-secondary); transform: translateY(-2px); }
        .auth-footer { text-align: center; margin-top: 25px; font-size: 14px; color: var(--text-secondary); }
        .auth-footer a { color: var(--brand-primary); text-decoration: none; font-weight: 600; }
        .auth-footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <div class="auth-card">
        <h2>Instructor Signup</h2>
        <div class="auth-subtitle">Register to add and manage quiz questions</div>
        
        <?php if (!empty($error)): ?>
            <div class="alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" name="register" class="btn-submit">Register Account</button>
        </form>
        
        <div class="auth-footer">
            Already have an account? <a href="supplier_login.php">Login here</a>
        </div>
    </div>

</body>
</html>