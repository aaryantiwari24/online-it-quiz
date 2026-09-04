<?php
session_start();
include '../include/db.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../authentication/customer_login.php");
    exit();
}

$requested_cat = isset($_REQUEST['category_id']) ? intval($_REQUEST['category_id']) : null;
$requested_diff = isset($_REQUEST['difficulty']) ? $_REQUEST['difficulty'] : null;

$valid_diffs = ['Easy', 'Medium', 'Hard'];
if ($requested_diff && !in_array($requested_diff, $valid_diffs)) {
    header("Location: dashboard.php");
    exit();
}

if ($requested_cat && $requested_diff && (!isset($_SESSION['quiz_category_id']) || $_SESSION['quiz_category_id'] != $requested_cat || $_SESSION['quiz_difficulty'] != $requested_diff)) {
    unset($_SESSION['quiz_questions']);
    unset($_SESSION['quiz_start_time']);
}

if (!isset($_SESSION['quiz_questions'])) {
    if ($requested_cat && $requested_diff) {
        
        // Remove 'status' check to match your database schema.
        // It relies purely on the 10-question minimum to determine if it is "Published".
        $count_stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM question WHERE category_id = ? AND difficulty = ?");
        mysqli_stmt_bind_param($count_stmt, "is", $requested_cat, $requested_diff);
        mysqli_stmt_execute($count_stmt);
        $count_res = mysqli_stmt_get_result($count_stmt);
        $count_row = mysqli_fetch_assoc($count_res);
        mysqli_stmt_close($count_stmt);

        if ($count_row['total'] < 10) {
            echo "<!DOCTYPE html><html><head><title>Insufficient Questions</title><link rel='stylesheet' href='../assets/style.css'></head>";
            echo "<body style='display:flex; justify-content:center; align-items:center; height:100vh; background:var(--bg-main); font-family:sans-serif;'>";
            echo "<div style='background:var(--surface-white); padding:40px; border-radius:12px; box-shadow:0 12px 30px rgba(0,0,0,0.05); text-align:center;'>";
            echo "<h2 style='margin-top:0;'>Insufficient Questions</h2>";
            echo "<p style='color:var(--text-secondary); margin-bottom:20px;'>This quiz requires 10 questions, but only {$count_row['total']} questions are available.</p>";
            echo "<a href='dashboard.php' style='display:inline-block; padding:12px 24px; background:var(--brand-primary); color:white; text-decoration:none; border-radius:8px; font-weight:600;'>Return to Dashboard</a>";
            echo "</div></body></html>";
            exit();
        }
        
        $stmt = mysqli_prepare($conn, "SELECT * FROM question WHERE category_id = ? AND difficulty = ? ORDER BY RAND() LIMIT 10");
        mysqli_stmt_bind_param($stmt, "is", $requested_cat, $requested_diff);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $questions = [];
        while($row = mysqli_fetch_assoc($result)) { 
            $questions[] = $row; 
        }
        mysqli_stmt_close($stmt);
        
        $_SESSION['quiz_questions'] = $questions;
        $_SESSION['quiz_category_id'] = $requested_cat;
        $_SESSION['quiz_difficulty'] = $requested_diff;
        $_SESSION['quiz_start_time'] = time();
    } else {
        header("Location: dashboard.php");
        exit();
    }
}

$questions = $_SESSION['quiz_questions'];
$total_questions = count($questions);
$time_limit = 10 * 60; 
$elapsed_time = time() - $_SESSION['quiz_start_time'];
$time_left = max(0, $time_limit - $elapsed_time);

if ($time_left <= 0) {
    header("Location: submit_quiz.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certification Exam - IT Quiz</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    <style>
        body { background-color: var(--bg-main); height: 100vh; overflow: hidden; display: flex; flex-direction: column; margin: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
        .quiz-header { background: var(--surface-white); padding: 18px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: var(--shadow-subtle); z-index: 10; border-bottom: 1px solid var(--border-light); }
        .quiz-title { font-size: 18px; font-weight: 800; color: var(--text-primary); margin: 0; letter-spacing: -0.5px; }
        .timer { font-size: 18px; font-weight: 800; color: var(--text-primary); background: var(--bg-main); padding: 8px 16px; border-radius: var(--radius-md); border: 1px solid var(--border-light); transition: var(--transition); }
        .timer.warning { color: #d97706; background: rgba(217, 119, 6, 0.1); border-color: rgba(217, 119, 6, 0.3); }
        .timer.critical { color: var(--accent-coral); background: rgba(255, 107, 74, 0.1); border-color: rgba(255, 107, 74, 0.3); animation: pulse 1s infinite; }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.8; } 100% { opacity: 1; } }
        .progress-container { height: 4px; background: var(--border-light); width: 100%; }
        .progress-bar { height: 100%; background: var(--brand-primary); width: 0%; transition: width 0.3s ease; }
        .quiz-layout { display: flex; flex: 1; overflow: hidden; max-width: 1400px; margin: 0 auto; width: 100%; gap: 30px; padding: 30px; box-sizing: border-box; }
        .question-area { flex: 3; background: var(--surface-white); border-radius: var(--radius-lg); box-shadow: var(--shadow-subtle); display: flex; flex-direction: column; border: 1px solid var(--border-light); overflow: hidden; }
        .question-card { display: none; padding: 40px; animation: fadeIn 0.3s ease; flex: 1; overflow-y: auto; }
        .question-card.active { display: block; }
        .q-number { font-size: 12px; color: var(--text-secondary); font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 12px; }
        .q-text { font-size: 22px; font-weight: 700; color: var(--text-primary); margin-bottom: 30px; line-height: 1.5; }
        .options-grid { display: flex; flex-direction: column; gap: 15px; }
        .option-label { display: flex; align-items: center; padding: 18px 20px; border: 2px solid var(--border-light); border-radius: var(--radius-md); cursor: pointer; transition: var(--transition); font-size: 16px; color: var(--text-primary); font-weight: 600; background: var(--bg-main); }
        .option-label:hover { border-color: var(--brand-primary); }
        .option-input { display: none; }
        .option-input:checked + .option-label { border-color: var(--brand-primary); background: rgba(81, 70, 229, 0.08); color: var(--brand-primary); box-shadow: 0 4px 12px rgba(81, 70, 229, 0.1); }
        .radio-circle { width: 20px; height: 20px; border: 2px solid var(--border-light); border-radius: 50%; margin-right: 15px; position: relative; transition: var(--transition); background: var(--surface-white); }
        .option-input:checked + .option-label .radio-circle { border-color: var(--brand-primary); }
        .option-input:checked + .option-label .radio-circle::after { content: ''; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 10px; height: 10px; background: var(--brand-primary); border-radius: 50%; }
        .quiz-footer { padding: 20px 40px; border-top: 1px solid var(--border-light); display: flex; justify-content: space-between; background: var(--bg-main); border-radius: 0 0 var(--radius-lg) var(--radius-lg); }
        .nav-panel { flex: 1; background: var(--surface-white); border-radius: var(--radius-lg); box-shadow: var(--shadow-subtle); padding: 30px; border: 1px solid var(--border-light); display: flex; flex-direction: column; max-width: 320px; }
        .nav-panel h3 { margin-top: 0; font-size: 16px; font-weight: 800; color: var(--text-primary); margin-bottom: 20px; }
        .nav-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; }
        .nav-btn { width: 100%; aspect-ratio: 1; border: 1px solid var(--border-light); background: var(--bg-main); border-radius: 8px; font-weight: 700; color: var(--text-secondary); cursor: pointer; transition: var(--transition); display: flex; align-items: center; justify-content: center; }
        .nav-btn:hover { border-color: var(--brand-primary); color: var(--brand-primary); }
        .nav-btn.answered { background: var(--brand-primary); color: white; border-color: var(--brand-primary); }
        .nav-btn.current { border: 2px solid var(--text-primary); color: var(--text-primary); background: var(--surface-white); }
        .btn-custom { padding: 12px 24px; border-radius: var(--radius-md); font-weight: 600; font-size: 14px; cursor: pointer; border: none; transition: var(--transition); }
        .btn-custom.primary { background: var(--brand-primary); color: white; box-shadow: 0 4px 12px rgba(81, 70, 229, 0.2); }
        .btn-custom.primary:hover { background: var(--brand-secondary); transform: translateY(-1px); }
        .btn-custom.outline { background: transparent; border: 1.5px solid var(--border-light); color: var(--text-primary); }
        .btn-custom.outline:hover:not(:disabled) { border-color: var(--text-primary); }
        .btn-custom:disabled { opacity: 0.5; cursor: not-allowed; }
    </style>
</head>
<body>

    <div class="progress-container">
        <div class="progress-bar" id="progressBar"></div>
    </div>

    <div class="quiz-header">
        <h1 class="quiz-title">Certification Evaluation</h1>
        <div class="timer" id="timerDisplay">--:--</div>
    </div>

    <form action="submit_quiz.php" method="POST" id="quizForm" class="quiz-layout">
        
        <div class="question-area">
            <?php foreach ($questions as $index => $q): $qNum = $index + 1; ?>
                <div class="question-card <?php echo $index === 0 ? 'active' : ''; ?>" id="q-<?php echo $qNum; ?>">
                    <div class="q-number">Question <?php echo $qNum; ?> of <?php echo $total_questions; ?></div>
                    <div class="q-text"><?php echo htmlspecialchars($q['question']); ?></div>
                    
                    <div class="options-grid">
                        <?php 
                        $options = ['a', 'b', 'c', 'd'];
                        foreach ($options as $opt): 
                            $optValue = $q['option_' . $opt];
                            if (!empty($optValue)):
                        ?>
                            <input type="radio" class="option-input" name="answer[<?php echo $q['question_id']; ?>]" id="q<?php echo $qNum . $opt; ?>" value="<?php echo $opt; ?>" onchange="markAnswered(<?php echo $qNum; ?>)">
                            <label class="option-label" for="q<?php echo $qNum . $opt; ?>">
                                <div class="radio-circle"></div>
                                <?php echo htmlspecialchars($optValue); ?>
                            </label>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="quiz-footer">
                <button type="button" class="btn-custom outline" id="btnPrev" onclick="navigate(-1)" disabled>Previous</button>
                <button type="button" class="btn-custom primary" id="btnNext" onclick="navigate(1)">Next Question</button>
                <button type="submit" class="btn-custom primary" id="btnSubmit" style="display: none; background: var(--accent-green);">Submit Exam</button>
            </div>
        </div>

        <div class="nav-panel">
            <h3>Question Map</h3>
            <div class="nav-grid">
                <?php for ($i = 1; $i <= $total_questions; $i++): ?>
                    <button type="button" class="nav-btn <?php echo $i === 1 ? 'current' : ''; ?>" id="nav-<?php echo $i; ?>" onclick="jumpTo(<?php echo $i; ?>)">
                        <?php echo $i; ?>
                    </button>
                <?php endfor; ?>
            </div>
            
            <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid var(--border-light);">
                <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 8px; font-size: 13px; color: var(--text-secondary);">
                    <div style="width: 14px; height: 14px; background: var(--brand-primary); border-radius: 4px;"></div> Answered
                </div>
                <div style="display: flex; gap: 10px; align-items: center; font-size: 13px; color: var(--text-secondary);">
                    <div style="width: 14px; height: 14px; background: var(--bg-main); border: 1px solid var(--border-light); border-radius: 4px;"></div> Unanswered
                </div>
            </div>
        </div>

    </form>

    <script>
        let currentQuestion = 1;
        const totalQuestions = <?php echo $total_questions; ?>;
        
        function updateUI() {
            document.querySelectorAll('.question-card').forEach(card => card.classList.remove('active'));
            document.getElementById(`q-${currentQuestion}`).classList.add('active');
            
            document.querySelectorAll('.nav-btn').forEach(btn => btn.classList.remove('current'));
            document.getElementById(`nav-${currentQuestion}`).classList.add('current');
            
            document.getElementById('btnPrev').disabled = (currentQuestion === 1);
            
            if (currentQuestion === totalQuestions) {
                document.getElementById('btnNext').style.display = 'none';
                document.getElementById('btnSubmit').style.display = 'block';
            } else {
                document.getElementById('btnNext').style.display = 'block';
                document.getElementById('btnSubmit').style.display = 'none';
            }
            
            const progress = ((currentQuestion - 1) / totalQuestions) * 100;
            document.getElementById('progressBar').style.width = `${progress}%`;
        }

        function navigate(direction) {
            currentQuestion += direction;
            updateUI();
        }

        function jumpTo(qNum) {
            currentQuestion = qNum;
            updateUI();
        }

        function markAnswered(qNum) {
            document.getElementById(`nav-${qNum}`).classList.add('answered');
        }

        let timeLeft = <?php echo $time_left; ?>;
        const timerDisplay = document.getElementById('timerDisplay');
        const form = document.getElementById('quizForm');

        const timer = setInterval(() => {
            timeLeft--;
            
            let minutes = Math.floor(timeLeft / 60);
            let seconds = timeLeft % 60;
            timerDisplay.innerText = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;

            if (timeLeft <= 300 && timeLeft > 60) {
                timerDisplay.className = 'timer warning'; 
            } else if (timeLeft <= 60) {
                timerDisplay.className = 'timer critical'; 
            }

            if (timeLeft <= 0) {
                clearInterval(timer);
                form.submit(); 
            }
        }, 1000);
    </script>
</body>
</html>