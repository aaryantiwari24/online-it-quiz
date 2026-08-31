<?php
session_start();
include '../include/db.php';

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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - IT Quiz</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body { display: flex; height: 100vh; overflow: hidden; background-color: var(--bg-main); font-family: 'Poppins', sans-serif; margin: 0; }
        
        /* Sidebar Layout */
        .sidebar { width: 260px; background: white; border-right: 1px solid #e2e8f0; display: flex; flex-direction: column; height: 100vh; }
        .sidebar-header { padding: 25px; font-size: 20px; font-weight: 700; color: var(--accent-primary); border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 10px; }
        .sidebar-menu { flex: 1; overflow-y: auto; padding: 20px 0; display: flex; flex-direction: column; gap: 5px; }
        .menu-item { padding: 12px 25px; color: var(--text-muted); text-decoration: none; font-size: 14px; font-weight: 500; transition: var(--transition); border-left: 3px solid transparent; display: flex; align-items: center; gap: 12px; cursor: pointer; }
        .menu-item:hover, .menu-item.active { background: #eef2ff; color: var(--accent-primary); border-left-color: var(--accent-primary); }
        .sidebar-footer { padding: 20px; border-top: 1px solid #f1f5f9; }
        .btn-logout { display: block; text-align: center; background: #fee2e2; color: #dc2626; text-decoration: none; padding: 10px; border-radius: 8px; font-weight: 500; font-size: 14px; transition: var(--transition); }
        .btn-logout:hover { background: #fca5a5; color: white; }

        /* Main Workspace */
        .main-content { flex: 1; overflow-y: auto; padding: 40px; }
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .user-badge { background: white; padding: 8px 16px; border-radius: 20px; border: 1px solid #e2e8f0; font-size: 14px; font-weight: 500; box-shadow: var(--shadow-sm); }

        /* Content Sections */
        .content-section { display: none; animation: fadeIn 0.3s ease; }
        .content-section.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

        /* Stats Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 35px; }
        .stat-box { background: white; padding: 25px; border-radius: var(--radius-md); border: 1px solid #e2e8f0; box-shadow: var(--shadow-sm); }
        .stat-box h3 { margin: 0 0 5px 0; font-size: 30px; color: var(--text-dark); }
        .stat-box p { margin: 0; color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; }

        /* Quiz Category Grid */
        .quiz-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
        .premium-card { background: white; padding: 25px; border-radius: var(--radius-lg); border: 1px solid #e2e8f0; box-shadow: var(--shadow-sm); }
        
        /* Tables */
        .table-container { background: white; border-radius: var(--radius-md); border: 1px solid #e2e8f0; overflow: hidden; box-shadow: var(--shadow-sm); }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 16px 20px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        th { background: #f8fafc; font-weight: 600; color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
        tr:last-child td { border-bottom: none; }
        tr:hover { background: #fafafa; }
        
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-pass { background: #d1fae5; color: #065f46; }
        .badge-fail { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <!-- Made the header a clickable link to the homepage -->
        <a href="../index.php" style="text-decoration: none;">
            <div class="sidebar-header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="var(--accent-primary)"><path d="M12 2L2 7l10 5 10-5-10-5zm0 7.5l-10-5v10l10 5 10-5v-10l-10 5z"/></svg>
                IT Quiz Student
            </div>
        </a>
        
        <div class="sidebar-menu">
            <!-- Added a dedicated Homepage button -->
            <a href="../index.php" class="menu-item" style="text-decoration: none;">
                🌐 Public Homepage
            </a>
            
            <div class="menu-item active" onclick="switchTab('dashboard', this)">📊 Dashboard Overview</div>
            <div class="menu-item" onclick="switchTab('quizzes', this)">🚀 Available Quizzes</div>
            <div class="menu-item" onclick="switchTab('history', this)">📜 Evaluation History</div>
            <div class="menu-item" onclick="switchTab('profile', this)">👤 My Profile</div>
        </div>
        
        <div class="sidebar-footer">
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </div>

    <!-- Main Content Panel -->
    <div class="main-content">
        <div class="header-top">
            <h2 id="page-title" style="margin:0;">Dashboard Overview</h2>
            <div class="user-badge">👤 <?php echo htmlspecialchars($user_data['name'] ?? 'Student'); ?></div>
        </div>

        <!-- 1. DASHBOARD OVERVIEW -->
        <div id="dashboard" class="content-section active">
            <p>Welcome back! Here is a summary of your platform performance.</p>
            
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
            <h2>Available Quizzes</h2>
            <p>Select a category to begin testing your skills.</p>
            
            <div class="quiz-grid">
                <?php
                if ($cat_result && mysqli_num_rows($cat_result) > 0) {
                    while ($cat = mysqli_fetch_assoc($cat_result)) {
                        ?>
                        <div class="premium-card" style="display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <h3 style="margin-top:0; color: var(--text-dark);"><?php echo htmlspecialchars($cat['category_name'] ?? ''); ?></h3>
                                <p><?php echo htmlspecialchars($cat['description'] ?? ''); ?></p>
                            </div>
                            <a href="quiz_difficulty.php?category_id=<?php echo $cat['category_id']; ?>" class="btn" style="text-align: center; margin-top: 15px; display: block; padding: 10px; background: var(--accent-primary); color: white; text-decoration: none; border-radius: var(--radius-md);">Start Quiz</a>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p>No categories found.</p>";
                }
                ?>
            </div>
        </div>

        <!-- 3. EVALUATION HISTORY -->
        <div id="history" class="content-section">
            <h2>Evaluation History</h2>
            <p>Review your previous scores and access your earned certificates.</p>
            
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
                                echo "<td>" . htmlspecialchars($history['category_name'] ?? 'Unknown') . "</td>";
                                echo "<td>" . $history['percentage'] . "%</td>";
                                echo "<td><span class='badge $badge'>" . $history['status'] . "</span></td>";
                                
                                if ($history['status'] == 'Pass') {
                                    echo "<td><a href='certificate.php?result_id=" . $history['result_id'] . "' style='color: var(--accent-primary); text-decoration: none; font-weight: 500;'>View Certificate</a></td>";
                                } else {
                                    echo "<td>-</td>";
                                }
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' style='text-align: center; color: var(--text-muted);'>No evaluation history available.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 4. PROFILE -->
        <div id="profile" class="content-section">
            <h2>My Profile</h2>
            <p>Your student account credentials.</p>
            
            <div class="premium-card" style="max-width: 450px;">
                <p style="margin: 0 0 10px 0;"><strong>Full Name:</strong> <?php echo htmlspecialchars($user_data['name'] ?? ''); ?></p>
                <p style="margin: 0 0 10px 0;"><strong>Email Address:</strong> <?php echo htmlspecialchars($user_data['email'] ?? ''); ?></p>
                <p style="margin: 0;"><strong>Student ID:</strong> #<?php echo str_pad($user_data['customer_id'] ?? 0, 5, '0', STR_PAD_LEFT); ?></p>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabId, element) {
            // Update active menu styling
            document.querySelectorAll('.menu-item').forEach(el => el.classList.remove('active'));
            element.classList.add('active');
            
            // Update header title based on text
            document.getElementById('page-title').innerText = element.innerText.replace(/[^a-zA-Z\s]/g, '').trim();

            // Switch active section panel
            document.querySelectorAll('.content-section').forEach(el => el.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
        }
    </script>
</body>
</html>