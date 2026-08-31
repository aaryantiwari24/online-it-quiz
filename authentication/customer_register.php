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
        $check_query = mysqli_query($conn, "SELECT * FROM customer WHERE email = '$email'");
        if (mysqli_num_rows($check_query) > 0) {
            $error = "Email is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_query = "INSERT INTO customer (name, email, password) VALUES ('$name', '$email', '$hashed_password')";
            
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Registration - IT Quiz</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body { background-color: var(--bg-main); font-family: 'Poppins', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: white; padding: 40px; border-radius: var(--radius-lg); border: 1px solid #e2e8f0; box-shadow: var(--shadow-sm); width: 100%; max-width: 400px; box-sizing: border-box; }
        .login-card h2 { margin-top: 0; font-size: 24px; color: var(--text-dark); margin-bottom: 25px; text-align: center; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 14px; font-weight: 500; color: var(--text-dark); margin-bottom: 8px; }
        .form-control { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: var(--radius-md); font-size: 14px; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        .form-control:focus { outline: none; border-color: var(--accent-primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        .alert-error { background: #fee2e2; color: #b91c1c; padding: 12px; border-radius: var(--radius-md); margin-bottom: 20px; font-size: 14px; font-weight: 500; text-align: center; }
        .alert-success { background: #d1fae5; color: #065f46; padding: 12px; border-radius: var(--radius-md); margin-bottom: 20px; font-size: 14px; font-weight: 500; text-align: center; }
        .auth-footer { text-align: center; margin-top: 20px; font-size: 14px; color: var(--text-muted); }
        .auth-footer a { color: var(--accent-primary); text-decoration: none; font-weight: 500; }
        .auth-footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <div class="login-card">
        <h2>Student Register</h2>
        
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
            <button type="submit" name="register" class="btn" style="width: 100%;">Register Account</button>
        </form>
        
        <div class="auth-footer">
            Already have an account? <a href="customer_login.php">Login here</a>
        </div>
    </div>

</body>
</html>