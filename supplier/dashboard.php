<?php
session_start();
include '../include/db.php';

// Check if supplier is logged in
if (!isset($_SESSION['supplier_id'])) {
    header("Location: ../authentication/supplier_login.php");
    exit();
}

$supplier_id = $_SESSION['supplier_id'];

// Fetch supplier details
$sup_query = mysqli_query($conn, "SELECT * FROM supplier WHERE supplier_id = $supplier_id");
$supplier_data = mysqli_fetch_assoc($sup_query);

// Metrics for this specific supplier
$total_questions_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM question WHERE supplier_id = '$supplier_id'");
$total_questions = mysqli_fetch_assoc($total_questions_query)['count'] ?? 0;

$total_categories_query = mysqli_query($conn, "SELECT COUNT(DISTINCT category_id) as count FROM question WHERE supplier_id = '$supplier_id'");
$total_categories = mysqli_fetch_assoc($total_categories_query)['count'] ?? 0;

// Dynamically calculate category progress for THIS supplier only
$progress_query = "SELECT c.category_name, 
                   SUM(CASE WHEN q.difficulty = 'Easy' THEN 1 ELSE 0 END) as easy_count,
                   SUM(CASE WHEN q.difficulty = 'Medium' THEN 1 ELSE 0 END) as med_count,
                   SUM(CASE WHEN q.difficulty = 'Hard' THEN 1 ELSE 0 END) as hard_count
                   FROM question q 
                   JOIN category c ON q.category_id = c.category_id 
                   WHERE q.supplier_id = ? 
                   GROUP BY c.category_id, c.category_name 
                   ORDER BY c.category_name ASC";
$stmt_prog = mysqli_prepare($conn, $progress_query);
mysqli_stmt_bind_param($stmt_prog, "i", $supplier_id);
mysqli_stmt_execute($stmt_prog);
$progress_result = mysqli_stmt_get_result($stmt_prog);

// Fetch recent questions added by this supplier
$recent_q_query = "SELECT q.*, c.category_name 
                   FROM question q 
                   LEFT JOIN category c ON q.category_id = c.category_id 
                   WHERE q.supplier_id = '$supplier_id' 
                   ORDER BY q.question_id DESC LIMIT 5";
$recent_questions = mysqli_query($conn, $recent_q_query);
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard - IT Quiz</title>
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
        .user-badge { background: var(--surface-white); padding: 10px 18px; border-radius: 30px; border: 1px solid var(--border-light); font-size: 14px; font-weight: 600; color: var(--text-primary); box-shadow: var(--shadow-subtle); display: flex; align-items: center; gap: 8px; }
        .metrics-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; margin-bottom: 40px; }
        .metric-card { background: var(--surface-white); padding: 30px; border-radius: var(--radius-lg); border: 1px solid var(--border-light); box-shadow: var(--shadow-subtle); }
        .metric-card h3 { margin: 0 0 10px 0; font-size: 12px; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px; font-weight: 700; }
        .metric-card .number { font-size: 36px; font-weight: 800; color: var(--text-primary); margin: 0; }
        .table-card { background: var(--surface-white); border-radius: var(--radius-lg); border: 1px solid var(--border-light); box-shadow: var(--shadow-subtle); padding: 30px; margin-bottom: 40px; }
        .table-card h2 { margin-top: 0; font-size: 18px; font-weight: 800; color: var(--text-primary); margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
        th { padding: 14px 18px; background: rgba(104, 112, 137, 0.04); color: var(--text-secondary); font-weight: 700; border-bottom: 1px solid var(--border-light); text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }
        td { padding: 16px 18px; border-bottom: 1px solid var(--border-light); color: var(--text-primary); }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(104, 112, 137, 0.02); }
        .badge { padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; background: rgba(81, 70, 229, 0.1); color: var(--brand-primary); }
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
            <a href="dashboard.php" class="sidebar-link active">📊 Dashboard</a>
            <a href="manage_question.php" class="sidebar-link">🧩 Manage Questions</a>
            <a href="profile.php" class="sidebar-link">⚙️ Profile Settings</a>
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
            <h1>Instructor Overview</h1>
            <div class="user-badge">👤 <?php echo htmlspecialchars($supplier_data['name'] ?? 'Instructor'); ?></div>
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
            <h2>Category Progress & Readiness</h2>
            <p style="color: var(--text-secondary); font-size: 13px; margin-top: -10px; margin-bottom: 20px;">A category is dynamically marked as "Complete" when you provide at least 10 questions per difficulty tier.</p>
            <table>
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Easy</th>
                        <th>Medium</th>
                        <th>Hard</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($progress_result) > 0): ?>
                        <?php while ($prog = mysqli_fetch_assoc($progress_result)): ?>
                            <?php 
                                $is_ready = ($prog['easy_count'] >= 10 && $prog['med_count'] >= 10 && $prog['hard_count'] >= 10);
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($prog['category_name']); ?></strong></td>
                                <td><?php echo $prog['easy_count']; ?>/10</td>
                                <td><?php echo $prog['med_count']; ?>/10</td>
                                <td><?php echo $prog['hard_count']; ?>/10</td>
                                <td>
                                    <?php if ($is_ready): ?>
                                        <span class="badge" style="background: rgba(24, 183, 122, 0.15); color: var(--accent-green);">Complete</span>
                                    <?php else: ?>
                                        <span class="badge" style="background: rgba(255, 107, 74, 0.15); color: var(--accent-coral);">Incomplete</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-secondary); padding: 40px;">No category progress found yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
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
                                <td><?php echo htmlspecialchars(substr($row['question'] ?? '', 0, 70)) . '...'; ?></td>
                                <td><strong><?php echo htmlspecialchars(ucfirst($row['difficulty'] ?? 'Standard')); ?></strong></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-secondary); padding: 40px;">No questions added yet. Start by visiting Manage Questions!</td>
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