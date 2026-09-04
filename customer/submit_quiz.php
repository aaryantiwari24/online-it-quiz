<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include '../include/db.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: dashboard.php");
    exit();
}

// =========================================================================
// 1. POST REQUEST HANDLER (Database Insert & Post-Redirect-Get pattern)
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Server-Side Duplicate Check using Token Verification
    $post_token = $_POST['quiz_token'] ?? '';
    $session_token = $_SESSION['quiz_token'] ?? '';

    // If tokens are missing, empty, or mismatched, the attempt is rejected
    if (empty($post_token) || empty($session_token) || !hash_equals($session_token, $post_token)) {
        die_duplicate_message();
    }

    if (!isset($_SESSION['quiz_questions'])) {
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

    // Store securely
    $insert_query = "INSERT INTO result (customer_id, category_id, difficulty, percentage, status, attempt_date) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($stmt, "iisiss", $customer_id, $category_id, $difficulty, $percentage, $status, $attempt_date);

    if (!mysqli_stmt_execute($stmt)) {
        die("Database Error: " . mysqli_error($conn));
    }
    $result_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    // Consume the token and clear the quiz session
    unset($_SESSION['quiz_token']);
    unset($_SESSION['quiz_questions']);
    unset($_SESSION['quiz_category_id']);
    unset($_SESSION['quiz_difficulty']);
    unset($_SESSION['quiz_start_time']);

    // Pass data forward via session to the GET view
    $_SESSION['quiz_review_display'] = [
        'result_id' => $result_id,
        'difficulty' => $difficulty,
        'percentage' => $percentage,
        'status' => $status,
        'correct_answers' => $correct_answers,
        'wrong_answers' => $wrong_answers,
        'total_questions' => $total_questions,
        'review_data' => $review_data
    ];

    // Force redirect (Post/Redirect/Get pattern)
    header("Location: submit_quiz.php");
    exit();
}

// =========================================================================
// 2. GET REQUEST HANDLER (Render HTML Results Safely)
// =========================================================================
function die_duplicate_message() {
    echo "<!DOCTYPE html><html lang='en' data-theme='light'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><title>Duplicate Submission</title><link href='https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap' rel='stylesheet'><link rel='stylesheet' href='../assets/style.css'></head>";
    echo "<body style='background-color: var(--bg-main); font-family: \"Plus Jakarta Sans\", sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0;'>";
    echo "<div style='background: var(--surface-white); padding: 40px; border-radius: var(--radius-lg); box-shadow: var(--shadow-subtle); text-align: center; border: 1px solid var(--border-light); max-width: 500px;'>";
    echo "<h2 style='color: var(--text-primary); margin-top: 0; font-size: 24px; font-weight: 800;'>Submission Error</h2>";
    echo "<p style='color: var(--text-secondary); margin-bottom: 30px; line-height: 1.6;'>This quiz attempt has already been submitted and securely recorded.</p>";
    echo "<a href='dashboard.php#history' style='display: inline-block; padding: 12px 24px; background: var(--brand-primary); color: white; text-decoration: none; border-radius: var(--radius-md); font-weight: 600; box-shadow: 0 4px 12px rgba(81,70,229,0.2);'>View Evaluation History</a>";
    echo "</div></body></html>";
    exit();
}

// If they access this script directly without a completed session view, send back.
if (!isset($_SESSION['quiz_review_display'])) {
    header("Location: dashboard.php");
    exit();
}

// Load data for the UI
$res = $_SESSION['quiz_review_display'];
$result_id = $res['result_id'];
$difficulty = $res['difficulty'];
$percentage = $res['percentage'];
$status = $res['status'];
$correct_answers = $res['correct_answers'];
$wrong_answers = $res['wrong_answers'];
$total_questions = $res['total_questions'];
$review_data = $res['review_data'];

?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Result - IT Quiz</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    <style>
        body { background-color: var(--bg-main); font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; padding: 40px 20px; display: flex; justify-content: center; }
        .result-container { max-width: 720px; width: 100%; display: flex; flex-direction: column; gap: 30px; }
        
        .result-card { background: var(--surface-white); padding: 50px; border-radius: var(--radius-lg); box-shadow: var(--shadow-subtle); text-align: center; border: 1px solid var(--border-light); }
        .score-circle { width: 140px; height: 140px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 38px; font-weight: 800; margin: 0 auto 30px auto; color: white; box-shadow: 0 10px 20px rgba(0,0,0,0.06); }
        .pass-circle { background: linear-gradient(135deg, var(--accent-green), #059669); }
        .fail-circle { background: linear-gradient(135deg, var(--accent-coral), #dc2626); }
        
        .result-title { font-size: 28px; font-weight: 800; margin-bottom: 8px; color: var(--text-primary); letter-spacing: -0.5px; }
        .result-subtitle { color: var(--text-secondary); margin-bottom: 30px; font-size: 16px; font-weight: 500; }
        
        .stats-row { display: flex; justify-content: space-around; margin-bottom: 40px; background: var(--bg-main); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-light); }
        .stat-box strong { display: block; font-size: 26px; font-weight: 800; color: var(--text-primary); margin-bottom: 4px; }
        .stat-box span { font-size: 11px; text-transform: uppercase; color: var(--text-secondary); font-weight: 700; letter-spacing: 1px; }
        
        .action-buttons { display: flex; gap: 15px; justify-content: center; }

        .review-card { background: var(--surface-white); padding: 40px; border-radius: var(--radius-lg); box-shadow: var(--shadow-subtle); border: 1px solid var(--border-light); }
        .review-card h2 { margin-top: 0; font-size: 20px; font-weight: 800; color: var(--text-primary); margin-bottom: 25px; }
        .review-item { padding: 20px; border-radius: var(--radius-md); margin-bottom: 15px; border: 1px solid var(--border-light); background: var(--bg-main); text-align: left; }
        .review-item.correct { border-left: 5px solid var(--accent-green); }
        .review-item.incorrect { border-left: 5px solid var(--accent-coral); }
        .q-title { font-weight: 700; color: var(--text-primary); margin-bottom: 12px; font-size: 15px; }
        .ans-details { font-size: 14px; color: var(--text-secondary); display: flex; flex-direction: column; gap: 6px; }
        .user-ans { font-weight: 700; }
        .user-ans.right { color: var(--accent-green); }
        .user-ans.wrong { color: var(--accent-coral); }

        .btn-custom { padding: 12px 24px; border-radius: var(--radius-md); font-weight: 600; font-size: 14px; text-decoration: none; transition: var(--transition); display: inline-block; }
        .btn-custom.primary { background: var(--brand-primary); color: white; box-shadow: 0 4px 12px rgba(81, 70, 229, 0.2); }
        .btn-custom.primary:hover { background: var(--brand-secondary); transform: translateY(-1px); }
        .btn-custom.outline { background: transparent; border: 1.5px solid var(--border-light); color: var(--text-primary); }
        .btn-custom.outline:hover { border-color: var(--text-primary); }
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
                    <strong style="color: var(--accent-green);"><?php echo $correct_answers; ?></strong>
                    <span>Correct</span>
                </div>
                <div class="stat-box">
                    <strong style="color: var(--accent-coral);"><?php echo $wrong_answers; ?></strong>
                    <span>Incorrect</span>
                </div>
                <div class="stat-box">
                    <strong><?php echo $total_questions; ?></strong>
                    <span>Total</span>
                </div>
            </div>

            <div class="action-buttons">
                <a href="dashboard.php" class="btn-custom outline">Back to Dashboard</a>
                <?php if ($status === 'Pass'): ?>
                    <a href="certificate.php?result_id=<?php echo $result_id; ?>" class="btn-custom primary" style="background: var(--accent-green);">View Certificate</a>
                <?php else: ?>
                    <a href="dashboard.php#quizzes" class="btn-custom primary">Try Again</a>
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
                            <div style="color: var(--accent-green);">
                                Correct Answer: <strong><?php echo $rev['correct_answer']; ?> (<?php echo htmlspecialchars($rev['option_' . strtolower($rev['correct_answer'])] ?? ''); ?>)</strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</body>
</html>