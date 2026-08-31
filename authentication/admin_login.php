<?php
session_start();
include '../include/db.php';

$error = "";

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    if (!empty($email) && !empty($password)) {
        // Query the 'admin' table
        $query = "SELECT * FROM admin WHERE email = '$email' LIMIT 1";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) == 1) {
            $admin = mysqli_fetch_assoc($result);
            
            // Verify password (supports hashed or plain text)
            if ($password === $admin['password'] || password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['admin_id'] ?? $admin['id'];
                $_SESSION['admin_email'] = $admin['email'];
                header("Location: ../admin/dashboard.php");
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "Admin account not found.";
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
    <title>Admin Login - IT Quiz</title>
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
    </style>
</head>
<body>

    <div class="login-card">
        <h2>Admin Login</h2>
        
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
            <button type="submit" name="login" class="btn" style="width: 100%;">Login to Dashboard</button>
        </form>
    </div>

</body>
</html>