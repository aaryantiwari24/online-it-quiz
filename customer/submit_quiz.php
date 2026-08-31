<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include '../include/db.php';

if (!isset($_SESSION['customer_id']) || !isset($_SESSION['quiz_questions'])) {
    header("Location: dashboard.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$category_id = $_SESSION['quiz_category_id'];
$difficulty = $_SESSION['quiz_difficulty'] ?? 'General';
$questions = $_SESSION['quiz_questions'];
$total_questions = count($questions);
$correct_answers = 0;

$submitted_answers = isset($_POST['answer']) ? $_POST['answer'] : [];

// Track detailed review data
$review_data = [];

foreach ($questions as $q) {
    $q_id = $q['question_id'];
    $actual_answer = strtolower(trim($q['correct_answer'])); 
    $user_answer = isset($submitted_answers[$q_id]) ? strtolower(trim($submitted_answers[$q_id])) : '';
    
    $is_correct = ($user_answer === $actual_answer);
    if ($is_correct) {
        $correct_answers++;
    }

    $review_data[] = [
        'question' => $q['question'],
        'option_a' => $q['option_a'],
        'option_b' => $q['option_b'],
        'option_c' => $q['option_c'],
        'option_d' => $q['option_d'],
        'user_answer' => strtoupper($user_answer),
        'correct_answer' => strtoupper($actual_answer),
        'is_correct' => $is_correct
    ];
}

$wrong_answers = $total_questions - $correct_answers;
$percentage = ($total_questions > 0) ? round(($correct_answers / $total_questions) * 100) : 0;
$status = ($percentage >= 60) ? 'Pass' : 'Fail';
$attempt_date = date('Y-m-d H:i:s');

// Save to Database
$insert_query = "INSERT INTO result (customer_id, category_id, difficulty, percentage, status, attempt_date) 
                 VALUES ($customer_id, $category_id, '$difficulty', $percentage, '$status', '$attempt_date')";

if (!mysqli_query($conn, $insert_query)) {
    die("Database Error: " . mysqli_error($conn));
}
$result_id = mysqli_insert_id($conn);

// Clear session to allow new quizzes
unset($_SESSION['quiz_questions']);
unset($_SESSION['quiz_category_id']);
unset($_SESSION['quiz_difficulty']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Result - IT Quiz</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body { background-color: var(--bg-main); font-family: 'Poppins', sans-serif; margin: 0; padding: 40px 0; display: flex; justify-content: center; }
        .result-container { max-width: 700px; width: 100%; display: flex; flex-direction: column; gap: 30px; }
        
        .result-card { background: var(--bg-card); padding: 50px; border-radius: var(--radius-lg); box-shadow: var(--shadow-hover); text-align: center; border: 1px solid #e2e8f0; }
        .score-circle { width: 150px; height: 150px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; font-weight: 700; margin: 0 auto 30px auto; color: white; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .pass-circle { background: linear-gradient(135deg, var(--accent-success), #059669); }
        .fail-circle { background: linear-gradient(135deg, #ef4444, #dc2626); }
        
        .result-title { font-size: 28px; margin-bottom: 10px; color: var(--text-dark); }
        .result-subtitle { color: var(--text-muted); margin-bottom: 30px; font-size: 16px; }
        
        .stats-row { display: flex; justify-content: space-around; margin-bottom: 40px; background: #f8fafc; padding: 20px; border-radius: var(--radius-md); border: 1px solid #f1f5f9; }
        .stat-box strong { display: block; font-size: 24px; color: var(--text-dark); margin-bottom: 5px; }
        .stat-box span { font-size: 12px; text-transform: uppercase; color: var(--text-muted); font-weight: 600; letter-spacing: 1px; }
        
        .action-buttons { display: flex; gap: 15px; justify-content: center; }

        /* Review Section */
        .review-card { background: var(--bg-card); padding: 40px; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid #e2e8f0; }
        .review-card h2 { margin-top: 0; font-size: 22px; color: var(--text-dark); margin-bottom: 25px; }
        .review-item { padding: 20px; border-radius: var(--radius-md); margin-bottom: 15px; border: 1px solid #e2e8f0; background: #f8fafc; }
        .review-item.correct { border-left: 5px solid #10b981; }
        .review-item.incorrect { border-left: 5px solid #ef4444; }
        .q-title { font-weight: 600; color: var(--text-dark); margin-bottom: 10px; font-size: 16px; }
        .ans-details { font-size: 14px; color: var(--text-muted); display: flex; gap: 20px; }
        .user-ans { font-weight: 600; }
        .user-ans.right { color: #10b981; }
        .user-ans.wrong { color: #ef4444; }
    </style>
</head>
<body>

    <div class="result-container">
        <!-- Score Card -->
        <div class="result-card">
            <?php if ($status === 'Pass'): ?>
                <div class="score-circle pass-circle"><?php echo $percentage; ?>%</div>
                <h1 class="result-title">🎉 Congratulations!</h1>
                <p class="result-subtitle">You successfully passed the <?php echo htmlspecialchars($difficulty); ?> certification evaluation.</p>
            <?php else: ?>
                <div class="score-circle fail-circle"><?php echo $percentage; ?>%</div>
                <h1 class="result-title">Keep Practicing</h1>
                <p class="result-subtitle">You did not meet the 60% passing requirement this time.</p>
            <?php endif; ?>

            <div class="stats-row">
                <div class="stat-box">
                    <strong style="color: var(--accent-success);"><?php echo $correct_answers; ?></strong>
                    <span>Correct</span>
                </div>
                <div class="stat-box">
                    <strong style="color: #ef4444;"><?php echo $wrong_answers; ?></strong>
                    <span>Incorrect</span>
                </div>
                <div class="stat-box">
                    <strong><?php echo $total_questions; ?></strong>
                    <span>Total</span>
                </div>
            </div>

            <div class="action-buttons">
                <a href="dashboard.php" class="btn btn-outline">Back to Dashboard</a>
                <?php if ($status === 'Pass'): ?>
                    <a href="certificate.php?result_id=<?php echo $result_id; ?>" class="btn" style="background: var(--accent-success);">View Certificate</a>
                <?php else: ?>
                    <a href="dashboard.php" class="btn">Try Again</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Detailed Answer Review -->
        <div class="review-card">
            <h2>Detailed Answer Review</h2>
            <?php foreach ($review_data as $index => $rev): $qNum = $index + 1; ?>
                <div class="review-item <?php echo $rev['is_correct'] ? 'correct' : 'incorrect'; ?>">
                    <div class="q-title">Q<?php echo $qNum; ?>: <?php echo htmlspecialchars($rev['question']); ?></div>
                    <div class="ans-details">
                        <div>
                            Your Answer: 
                            <span class="user-ans <?php echo $rev['is_correct'] ? 'right' : 'wrong'; ?>">
                                <?php echo $rev['user_answer'] ? $rev['user_answer'] . ' (' . htmlspecialchars($rev['option_' . strtolower($rev['user_answer'])] ?? '') . ')' : 'Unanswered'; ?>
                            </span>
                        </div>
                        <?php if (!$rev['is_correct']): ?>
                            <div>
                                Correct Answer: <strong style="color: #10b981;"><?php echo $rev['correct_answer']; ?> (<?php echo htmlspecialchars($rev['option_' . strtolower($rev['correct_answer'])]); ?>)</strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</body>
</html>