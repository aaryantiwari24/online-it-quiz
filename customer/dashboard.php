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

// Fetch User Details
$user_query = "SELECT * FROM customer WHERE customer_id = $customer_id";
$user_result = mysqli_query($conn, $user_query);
$user_data = mysqli_fetch_assoc($user_result);

// Fetch Categories
$cat_query = "SELECT * FROM category ORDER BY category_name ASC";
$cat_result = mysqli_query($conn, $cat_query);

// Fetch Quiz History
$history_query = "SELECT r.*, c.category_name 
                  FROM result r 
                  LEFT JOIN category c ON r.category_id = c.category_id 
                  WHERE r.customer_id = $customer_id 
                  ORDER BY r.attempt_date DESC";
$history_result = mysqli_query($conn, $history_query);

// Calculate Statistics
$stats_query = "SELECT 
                COUNT(result_id) as total_taken, 
                SUM(CASE WHEN status = 'Pass' THEN 1 ELSE 0 END) as total_passed,
                AVG(percentage) as avg_score
                FROM result WHERE customer_id = $customer_id";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

$total_taken = $stats['total_taken'] ?? 0;
$total_passed = $stats['total_passed'] ?? 0;
$avg_score = ($total_taken > 0) ? round($stats['avg_score']) : 0;
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - IT Quiz</title>
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

        /* Theme Toggle in Sidebar Footer */
        .theme-toggle { background: none; border: 1px solid var(--border-light); width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-primary); transition: var(--transition); }
        .theme-toggle:hover { background: var(--border-light); }

        /* Main Workspace */
        .main-content { flex: 1; overflow-y: auto; padding: 40px; background-color: var(--bg-main); }
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; }
        .header-top h2 { font-size: 26px; font-weight: 800; color: var(--text-primary); letter-spacing: -0.5px; margin: 0; }
        .user-badge { background: var(--surface-white); padding: 10px 18px; border-radius: 30px; border: 1px solid var(--border-light); font-size: 14px; font-weight: 600; color: var(--text-primary); box-shadow: var(--shadow-subtle); display: flex; align-items: center; gap: 8px; }

        /* Content Sections */
        .content-section { display: none; animation: fadeIn 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); }
        .content-section.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

        /* Stats Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; margin-bottom: 40px; }
        .stat-box { background: var(--surface-white); padding: 30px; border-radius: var(--radius-lg); border: 1px solid var(--border-light); box-shadow: var(--shadow-subtle); }
        .stat-box h3 { margin: 0 0 8px 0; font-size: 36px; font-weight: 800; color: var(--text-primary); }
        .stat-box p { margin: 0; color: var(--text-secondary); font-size: 12px; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; }

        /* Quiz Category Grid */
        .quiz-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; }
        .premium-card { background: var(--surface-white); padding: 30px; border-radius: var(--radius-lg); border: 1px solid var(--border-light); box-shadow: var(--shadow-subtle); transition: var(--transition); }
        .premium-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-hover); border-color: var(--brand-primary); }
        
        /* Tables */
        .table-container { background: var(--surface-white); border-radius: var(--radius-lg); border: 1px solid var(--border-light); overflow: hidden; box-shadow: var(--shadow-subtle); }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 18px 24px; border-bottom: 1px solid var(--border-light); font-size: 14px; color: var(--text-primary); }
        th { background: rgba(104, 112, 137, 0.04); font-weight: 700; color: var(--text-secondary); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(104, 112, 137, 0.02); }
        
        .badge { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; display: inline-block; }
        .badge-pass { background: rgba(24, 183, 122, 0.15); color: var(--accent-green); }
        .badge-fail { background: rgba(255, 107, 74, 0.15); color: var(--accent-coral); }

        .btn-action { display: inline-block; padding: 12px 24px; background: var(--brand-primary); color: white; text-decoration: none; border-radius: var(--radius-md); font-weight: 600; font-size: 14px; transition: var(--transition); text-align: center; border: none; cursor: pointer; }
        .btn-action:hover { background: var(--brand-secondary); transform: translateY(-1px); }
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
            <a href="../index.php" class="menu-item">
                🏠 Home
            </a>
            
            <div class="menu-item active" onclick="switchTab('dashboard', this)">📊 Dashboard Overview</div>
            <div class="menu-item" onclick="switchTab('quizzes', this)" id="menu-quizzes">🧩 Available Quizzes</div>
            <div class="menu-item" onclick="switchTab('history', this)">📜 Evaluation History</div>
            <a href="profile.php" class="menu-item">⚙️ My Profile</a>
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
            <h2 id="page-title">Dashboard Overview</h2>
            <div class="user-badge">👤 <?php echo htmlspecialchars($user_data['name'] ?? 'Student'); ?></div>
        </div>

        <!-- 1. DASHBOARD OVERVIEW -->
        <div id="dashboard" class="content-section active">
            <p style="color: var(--text-secondary); margin-bottom: 30px;">Welcome back! Here is a summary of your platform performance and progress.</p>
            
            <div class="stats-grid">
                <div class="stat-box">
                    <h3><?php echo $total_taken; ?></h3>
                    <p>Total Attempts</p>
                </div>
                <div class="stat-box">
                    <h3><?php echo $total_passed; ?></h3>
                    <p>Certificates Earned</p>
                </div>
                <div class="stat-box">
                    <h3><?php echo $avg_score; ?>%</h3>
                    <p>Average Score</p>
                </div>
            </div>
        </div>

        <!-- 2. AVAILABLE QUIZZES -->
        <div id="quizzes" class="content-section">
            <h2 style="font-size: 22px; font-weight: 800; color: var(--text-primary); margin-bottom: 8px;">Available Quizzes</h2>
            <p style="color: var(--text-secondary); margin-bottom: 30px;">Select a category to begin testing your skills across different tiers.</p>
            
            <div class="quiz-grid">
                <?php
                if ($cat_result && mysqli_num_rows($cat_result) > 0) {
                    while ($cat = mysqli_fetch_assoc($cat_result)) {
                        ?>
                        <div class="premium-card" style="display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <h3 style="margin-top:0; color: var(--text-primary); font-size: 20px; font-weight: 700; margin-bottom: 10px;"><?php echo htmlspecialchars($cat['category_name'] ?? ''); ?></h3>
                                <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.5; margin-bottom: 20px;"><?php echo htmlspecialchars($cat['description'] ?? 'Take an evaluation in this category to test your technical competency.'); ?></p>
                            </div>
                            <a href="quiz_difficulty.php?category_id=<?php echo $cat['category_id']; ?>" class="btn-action">Start Quiz →</a>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p style='color: var(--text-secondary);'>No categories found.</p>";
                }
                ?>
            </div>
        </div>

        <!-- 3. EVALUATION HISTORY -->
        <div id="history" class="content-section">
            <h2 style="font-size: 22px; font-weight: 800; color: var(--text-primary); margin-bottom: 8px;">Evaluation History</h2>
            <p style="color: var(--text-secondary); margin-bottom: 30px;">Review your previous scores and access your verified certificates.</p>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Score</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($history_result && mysqli_num_rows($history_result) > 0) {
                            while ($history = mysqli_fetch_assoc($history_result)) {
                                $badge = ($history['status'] == 'Pass') ? 'badge-pass' : 'badge-fail';
                                echo "<tr>";
                                echo "<td>" . date('M d, Y', strtotime($history['attempt_date'])) . "</td>";
                                echo "<td><strong>" . htmlspecialchars($history['category_name'] ?? 'Unknown') . "</strong></td>";
                                echo "<td><strong>" . $history['percentage'] . "%</strong></td>";
                                echo "<td><span class='badge $badge'>" . $history['status'] . "</span></td>";
                                
                                if ($history['status'] == 'Pass') {
                                    echo "<td><a href='certificate.php?result_id=" . $history['result_id'] . "' style='color: var(--brand-primary); text-decoration: none; font-weight: 700;'>View Certificate →</a></td>";
                                } else {
                                    echo "<td style='color: var(--text-secondary);'>-</td>";
                                }
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' style='text-align: center; color: var(--text-secondary); padding: 40px;'>No evaluation history available yet.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Dark Mode Toggle Logic
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

        function switchTab(tabId, element) {
            document.querySelectorAll('.menu-item').forEach(el => el.classList.remove('active'));
            element.classList.add('active');
            
            document.getElementById('page-title').innerText = element.innerText.replace(/[^a-zA-Z\s]/g, '').trim();

            document.querySelectorAll('.content-section').forEach(el => el.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
        }

        // Auto-switch to quizzes tab if hash is present in URL (e.g. #quizzes)
        if (window.location.hash === '#quizzes') {
            switchTab('quizzes', document.getElementById('menu-quizzes'));
        }
    </script>
</body>
</html>