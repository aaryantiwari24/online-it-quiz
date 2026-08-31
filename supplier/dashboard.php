<?php
session_start();
include '../include/db.php';

// Check if supplier is logged in
if (!isset($_SESSION['supplier_id'])) {
    header("Location: ../authentication/supplier_login.php");
    exit();
}

$supplier_id = $_SESSION['supplier_id'];

// Metrics for this specific supplier
$total_questions_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM question WHERE supplier_id = '$supplier_id'");
$total_questions = mysqli_fetch_assoc($total_questions_query)['count'] ?? 0;

$total_categories_query = mysqli_query($conn, "SELECT COUNT(DISTINCT category_id) as count FROM question WHERE supplier_id = '$supplier_id'");
$total_categories = mysqli_fetch_assoc($total_categories_query)['count'] ?? 0;

// Fetch recent questions added by this supplier
$recent_q_query = "SELECT q.*, c.category_name 
                   FROM question q 
                   LEFT JOIN category c ON q.category_id = c.category_id 
                   WHERE q.supplier_id = '$supplier_id' 
                   ORDER BY q.question_id DESC LIMIT 5";
$recent_questions = mysqli_query($conn, $recent_q_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Dashboard - IT Quiz</title>
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
        .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; }
        .dashboard-header h1 { font-size: 26px; color: var(--text-dark); margin: 0; }
        .supplier-badge { background: #ecfdf5; color: #059669; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; }

        .metrics-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 40px; }
        .metric-card { background: white; padding: 25px; border-radius: var(--radius-lg); border: 1px solid #e2e8f0; box-shadow: var(--shadow-sm); }
        .metric-card h3 { margin: 0 0 10px 0; font-size: 13px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }
        .metric-card .number { font-size: 32px; font-weight: 700; color: var(--text-dark); margin: 0; }

        .table-card { background: white; border-radius: var(--radius-lg); border: 1px solid #e2e8f0; box-shadow: var(--shadow-sm); padding: 30px; }
        .table-card h2 { margin-top: 0; font-size: 18px; color: var(--text-dark); margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 15px; }
        th { padding: 12px 16px; background: #f8fafc; color: var(--text-muted); font-weight: 600; border-bottom: 2px solid #e2e8f0; }
        td { padding: 14px 16px; border-bottom: 1px solid #e2e8f0; color: var(--text-dark); }
        tr:hover td { background: #f8fafc; }
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; background: #eff6ff; color: var(--accent-primary); }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-brand">IT Quiz Creator</div>
        <div class="sidebar-menu">
            <a href="dashboard.php" class="sidebar-link active">Dashboard</a>
            <a href="manage_question.php" class="sidebar-link">Manage Questions</a>
            <a href="profile.php" class="sidebar-link">Profile</a>
        </div>
        <a href="logout.php" class="sidebar-logout">Logout</a>
    </div>

    <div class="main-content">
        <div class="dashboard-header">
            <h1>Supplier Dashboard</h1>
            <div class="supplier-badge">Instructor Account</div>
        </div>

        <div class="metrics-grid">
            <div class="metric-card">
                <h3>My Total Questions</h3>
                <p class="number"><?php echo $total_questions; ?></p>
            </div>
            <div class="metric-card">
                <h3>Categories Covered</h3>
                <p class="number"><?php echo $total_categories; ?></p>
            </div>
        </div>

        <div class="table-card">
            <h2>Recently Added Questions</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category</th>
                        <th>Question</th>
                        <th>Difficulty</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($recent_questions) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($recent_questions)): ?>
                            <tr>
                                <td>#<?php echo $row['question_id']; ?></td>
                                <td><span class="badge"><?php echo htmlspecialchars($row['category_name'] ?? 'General'); ?></span></td>
                                <td><?php echo htmlspecialchars(substr($row['question_text'] ?? $row['question'] ?? '', 0, 70)) . '...'; ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($row['difficulty'] ?? 'Standard')); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 30px;">No questions added yet. Start by visiting Manage Questions!</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>