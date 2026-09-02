<?php
// Prevent browser form resubmission error on back button click
header("Cache-Control: private, must-revalidate, max-age=0");

session_start();
include '../include/db.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../authentication/customer_login.php");
    exit();
}

$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$cat_query = mysqli_query($conn, "SELECT * FROM category WHERE category_id = $category_id");
$category = mysqli_fetch_assoc($cat_query);

if (!$category) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Difficulty - <?php echo htmlspecialchars($category['category_name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    <style>
        body { background-color: var(--bg-main); display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
        .difficulty-container { background: var(--surface-white); padding: 50px; border-radius: var(--radius-lg); box-shadow: var(--shadow-hover); max-width: 720px; width: 100%; border: 1px solid var(--border-light); text-align: center; box-sizing: border-box; }
        .difficulty-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 35px; }
        .diff-card { background: var(--bg-main); border: 2px solid var(--border-light); border-radius: var(--radius-md); padding: 30px 20px; text-decoration: none; color: var(--text-primary); transition: var(--transition); display: block; position: relative; overflow: hidden; }
        .diff-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-subtle); border-color: var(--brand-primary); }
        .diff-badge { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; display: inline-block; padding: 4px 12px; border-radius: 20px; }
        .easy .diff-badge { background: rgba(24, 183, 122, 0.15); color: var(--accent-green); }
        .medium .diff-badge { background: rgba(108, 99, 255, 0.15); color: var(--brand-secondary); }
        .hard .diff-badge { background: rgba(255, 107, 74, 0.15); color: var(--accent-coral); }
        .diff-card h3 { margin: 0 0 10px 0; font-size: 20px; color: var(--text-primary); font-weight: 700; }
        .diff-card p { margin: 0; font-size: 13px; color: var(--text-secondary); line-height: 1.4; }
        .btn-outline-custom { display: inline-block; padding: 12px 28px; border: 1.5px solid var(--border-light); border-radius: var(--radius-md); color: var(--text-primary); text-decoration: none; font-weight: 600; font-size: 14px; transition: var(--transition); background: transparent; }
        .btn-outline-custom:hover { border-color: var(--brand-primary); color: var(--brand-primary); }
    </style>
</head>
<body>

    <div class="difficulty-container">
        <span style="font-size: 13px; font-weight: 700; color: var(--brand-primary); text-transform: uppercase; letter-spacing: 2px;">Certification Exam</span>
        <h1 style="margin: 10px 0 15px 0; color: var(--text-primary); font-size: 32px; font-weight: 800; letter-spacing: -0.5px;"><?php echo htmlspecialchars($category['category_name']); ?></h1>
        <p style="color: var(--text-secondary); font-size: 15px; margin-bottom: 10px;">Select your preferred evaluation tier. Each test consists of 10 targeted questions with a strict time limit.</p>

        <div class="difficulty-grid">
            <a href="quiz.php?category_id=<?php echo $category_id; ?>&difficulty=Easy" class="diff-card easy">
                <div class="diff-badge">Tier 1</div>
                <h3>Easy</h3>
                <p>Core fundamentals and basic concepts.</p>
            </a>

            <a href="quiz.php?category_id=<?php echo $category_id; ?>&difficulty=Medium" class="diff-card medium">
                <div class="diff-badge">Tier 2</div>
                <h3>Medium</h3>
                <p>Intermediate logic and practical application.</p>
            </a>

            <a href="quiz.php?category_id=<?php echo $category_id; ?>&difficulty=Hard" class="diff-card hard">
                <div class="diff-badge">Tier 3</div>
                <h3>Hard</h3>
                <p>Advanced scenarios and expert problem solving.</p>
            </a>
        </div>

        <div style="margin-top: 40px;">
            <a href="dashboard.php#quizzes" class="btn-outline-custom">Back to Dashboard</a>
        </div>
    </div>

</body>
</html>