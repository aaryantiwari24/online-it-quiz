<?php
session_start();
include '../include/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../authentication/admin_login.php");
    exit();
}

if (isset($_GET['delete'])) {
    $supplier_id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM supplier WHERE supplier_id = $supplier_id");
    header("Location: manage_suppliers.php");
    exit();
}

$suppliers = mysqli_query($conn, "SELECT * FROM supplier ORDER BY supplier_id DESC");
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Suppliers - IT Quiz Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    <style>
        body { background-color: var(--bg-main); font-family: 'Poppins', sans-serif; margin: 0; display: flex; height: 100vh; overflow: hidden; }
        
        .sidebar { width: 280px; background: var(--surface-white); border-right: 1px solid var(--border-light); display: flex; flex-direction: column; padding: 25px 20px; box-sizing: border-box; height: 100vh; }
        .sidebar-brand { font-size: 18px; font-weight: 800; color: var(--text-primary); margin-bottom: 30px; display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .logo-icon { width: 32px; height: 32px; background: var(--brand-primary); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; }
        
        .sidebar-menu { display: flex; flex-direction: column; gap: 6px; flex: 1; overflow-y: auto; }
        .sidebar-link { padding: 14px 16px; border-radius: var(--radius-md); text-decoration: none; color: var(--text-secondary); font-weight: 600; font-size: 14px; transition: var(--transition); border-left: 3px solid transparent; display: flex; align-items: center; gap: 10px; }
        .sidebar-link:hover, .sidebar-link.active { background: rgba(81, 70, 229, 0.08); color: var(--brand-primary); border-left-color: var(--brand-primary); }
        
        .sidebar-footer { padding-top: 20px; border-top: 1px solid var(--border-light); display: flex; align-items: center; justify-content: space-between; }
        .sidebar-logout { padding: 10px 16px; border-radius: 8px; text-decoration: none; color: var(--accent-coral); font-weight: 600; font-size: 13px; transition: var(--transition); background: rgba(255, 107, 74, 0.1); border: 1px solid rgba(255, 107, 74, 0.2); }
        .sidebar-logout:hover { background: var(--accent-coral); color: white; }

        .theme-toggle { background: none; border: 1px solid var(--border-light); width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-primary); transition: var(--transition); }
        .theme-toggle:hover { background: var(--border-light); }

        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; padding: 40px; box-sizing: border-box; background-color: var(--bg-main); }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; }
        .page-header h1 { font-size: 26px; font-weight: 800; color: var(--text-primary); margin: 0; letter-spacing: -0.5px; }

        .table-card { background: var(--surface-white); border-radius: var(--radius-lg); border: 1px solid var(--border-light); box-shadow: var(--shadow-subtle); padding: 30px; }
        
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
        th { padding: 14px 18px; background: rgba(104, 112, 137, 0.04); color: var(--text-secondary); font-weight: 700; border-bottom: 1px solid var(--border-light); text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }
        td { padding: 16px 18px; border-bottom: 1px solid var(--border-light); color: var(--text-primary); }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(104, 112, 137, 0.02); }
        
        .btn-danger { background: rgba(255, 107, 74, 0.1); color: var(--accent-coral); border: 1px solid rgba(255, 107, 74, 0.3); padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; transition: var(--transition); display: inline-block; }
        .btn-danger:hover { background: var(--accent-coral); color: white; }
    </style>
</head>
<body>

    <div class="sidebar">
        <a href="../index.php" class="sidebar-brand">
            <div class="logo-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            </div>
            IT Quiz Admin
        </a>
        <div class="sidebar-menu">
            <a href="../index.php" class="sidebar-link">🏠 Home</a>
            <a href="dashboard.php" class="sidebar-link">📊 Dashboard</a>
            <a href="manage_customers.php" class="sidebar-link">👥 Customers</a>
            <a href="manage_suppliers.php" class="sidebar-link active">🏢 Suppliers</a>
            <a href="manage_category.php" class="sidebar-link">📂 Categories</a>
            <a href="view_results.php" class="sidebar-link">📈 Results</a>
            <a href="manage_certificates.php" class="sidebar-link">🎓 Certificates</a>
            <a href="profile.php" class="sidebar-link">⚙️ Profile</a>
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
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($suppliers) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($suppliers)): ?>
                            <tr>
                                <td>#<?php echo $row['supplier_id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                <td style="color: var(--text-secondary);"><?php echo htmlspecialchars($row['email']); ?></td>
                                <td style="text-align: right;">
                                    <a href="manage_suppliers.php?delete=<?php echo $row['supplier_id']; ?>" class="btn-danger" onclick="return confirm('Are you sure you want to delete this supplier?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-secondary); padding: 30px;">No registered suppliers found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
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