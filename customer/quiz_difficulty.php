<?php
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Difficulty - <?php echo htmlspecialchars($category['category_name']); ?></title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body { background-color: #f1f5f9; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; font-family: 'Poppins', sans-serif; }
        .difficulty-container { background: white; padding: 50px; border-radius: var(--radius-lg); box-shadow: var(--shadow-hover); max-width: 700px; width: 100%; border: 1px solid #e2e8f0; text-align: center; box-sizing: border-box; }
        .difficulty-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 35px; }
        .diff-card { background: #f8fafc; border: 2px solid #e2e8f0; border-radius: var(--radius-md); padding: 30px 20px; text-decoration: none; color: var(--text-dark); transition: var(--transition); display: block; position: relative; overflow: hidden; }
        .diff-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-sm); }
        .diff-card.easy:hover { border-color: #10b981; background: #ecfdf5; }
        .diff-card.medium:hover { border-color: #f59e0b; background: #fffbeb; }
        .diff-card.hard:hover { border-color: #ef4444; background: #fef2f2; }
        .diff-badge { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; display: inline-block; padding: 4px 10px; border-radius: 20px; }
        .easy .diff-badge { background: #d1fae5; color: #065f46; }
        .medium .diff-badge { background: #fef3c7; color: #b45309; }
        .hard .diff-badge { background: #fee2e2; color: #b91c1c; }
        .diff-card h3 { margin: 0 0 10px 0; font-size: 20px; color: var(--text-dark); }
        .diff-card p { margin: 0; font-size: 13px; color: var(--text-muted); line-height: 1.4; }
    </style>
</head>
<body>

    <div class="difficulty-container">
        <span style="font-size: 13px; font-weight: 600; color: var(--accent-primary); text-transform: uppercase; letter-spacing: 2px;">Certification Exam</span>
        <h1 style="margin: 10px 0 15px 0; color: var(--text-dark); font-size: 28px;"><?php echo htmlspecialchars($category['category_name']); ?></h1>
        <p style="color: var(--text-muted); font-size: 15px; margin-bottom: 10px;">Select your preferred evaluation tier. Each test consists of 10 targeted questions with a strict time limit.</p>

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
            <a href="dashboard.php" class="btn btn-outline">Back to Dashboard</a>
        </div>
    </div>

</body>
</html>