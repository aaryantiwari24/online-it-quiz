<?php
session_start();
include '../include/db.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../authentication/customer_login.php");
    exit();
}

// 1. Grab variables whether they come from a Form (POST) or a Link (GET)
$requested_cat = isset($_REQUEST['category_id']) ? $_REQUEST['category_id'] : null;
$requested_diff = isset($_REQUEST['difficulty']) ? $_REQUEST['difficulty'] : null;

// 2. If a user clicks a new difficulty link, destroy the old quiz session
if ($requested_cat && $requested_diff) {
    unset($_SESSION['quiz_questions']); 
}

// 3. Generate the 10 questions for the specific difficulty
if (!isset($_SESSION['quiz_questions'])) {
    if ($requested_cat && $requested_diff) {
        $questions = [];
        
        // Fetch EXACTLY 10 questions for the specific difficulty chosen
        $query = "SELECT * FROM question WHERE category_id = $requested_cat AND difficulty = '$requested_diff' ORDER BY RAND() LIMIT 10";
        $result = mysqli_query($conn, $query);
        
        while($row = mysqli_fetch_assoc($result)) { 
            $questions[] = $row; 
        }
        
        $_SESSION['quiz_questions'] = $questions;
        $_SESSION['quiz_category_id'] = $requested_cat;
        $_SESSION['quiz_difficulty'] = $requested_diff;
    } else {
        header("Location: dashboard.php");
        exit();
    }
}

$questions = $_SESSION['quiz_questions'];
$total_questions = count($questions);
$time_limit = 10 * 60; // 10 minutes for the quiz
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certification Exam - IT Quiz</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body { background-color: #f1f5f9; height: 100vh; overflow: hidden; display: flex; flex-direction: column; margin: 0; }
        
        .quiz-header { background: white; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: var(--shadow-sm); z-index: 10; }
        .quiz-title { font-size: 18px; font-weight: 600; color: var(--text-dark); margin: 0; }
        .timer { font-size: 20px; font-weight: 700; color: var(--text-dark); background: #f8fafc; padding: 8px 16px; border-radius: 8px; border: 1px solid #e2e8f0; transition: var(--transition); }
        .timer.warning { color: #d97706; background: #fef3c7; border-color: #fde68a; }
        .timer.critical { color: var(--accent-danger); background: #fee2e2; border-color: #fca5a5; animation: pulse 1s infinite; }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.8; } 100% { opacity: 1; } }

        .progress-container { height: 4px; background: #e2e8f0; width: 100%; }
        .progress-bar { height: 100%; background: var(--accent-primary); width: 0%; transition: width 0.3s ease; }

        .quiz-layout { display: flex; flex: 1; overflow: hidden; max-width: 1400px; margin: 0 auto; width: 100%; gap: 30px; padding: 30px; box-sizing: border-box; }
        
        .question-area { flex: 3; background: white; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); display: flex; flex-direction: column; border: 1px solid #e2e8f0; overflow: hidden; }
        .question-card { display: none; padding: 40px; animation: fadeIn 0.3s ease; flex: 1; overflow-y: auto; }
        .question-card.active { display: block; }
        .q-number { font-size: 14px; color: var(--text-muted); font-weight: 600; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 10px; }
        .q-text { font-size: 22px; font-weight: 500; color: var(--text-dark); margin-bottom: 30px; line-height: 1.5; }

        .options-grid { display: flex; flex-direction: column; gap: 15px; }
        .option-label { display: flex; align-items: center; padding: 16px 20px; border: 2px solid #e2e8f0; border-radius: var(--radius-md); cursor: pointer; transition: var(--transition); font-size: 16px; color: var(--text-dark); font-weight: 500; }
        .option-label:hover { border-color: #cbd5e1; background: #f8fafc; }
        .option-input { display: none; }
        .option-input:checked + .option-label { border-color: var(--accent-primary); background: #eff6ff; box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.1); }
        .radio-circle { width: 20px; height: 20px; border: 2px solid #cbd5e1; border-radius: 50%; margin-right: 15px; position: relative; transition: var(--transition); }
        .option-input:checked + .option-label .radio-circle { border-color: var(--accent-primary); }
        .option-input:checked + .option-label .radio-circle::after { content: ''; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 10px; height: 10px; background: var(--accent-primary); border-radius: 50%; }

        .quiz-footer { padding: 20px 40px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; background: #f8fafc; border-radius: 0 0 var(--radius-lg) var(--radius-lg); }
        
        .nav-panel { flex: 1; background: white; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); padding: 30px; border: 1px solid #e2e8f0; display: flex; flex-direction: column; }
        .nav-panel h3 { margin-top: 0; font-size: 16px; margin-bottom: 20px; }
        .nav-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; }
        .nav-btn { width: 100%; aspect-ratio: 1; border: 1px solid #e2e8f0; background: white; border-radius: 8px; font-weight: 600; color: var(--text-muted); cursor: pointer; transition: var(--transition); display: flex; align-items: center; justify-content: center; }
        .nav-btn:hover { border-color: var(--accent-primary); color: var(--accent-primary); }
        .nav-btn.answered { background: var(--accent-primary); color: white; border-color: var(--accent-primary); }
        .nav-btn.current { border: 2px solid var(--text-dark); color: var(--text-dark); background: #f8fafc; }
    </style>
</head>
<body>

    <div class="progress-container">
        <div class="progress-bar" id="progressBar"></div>
    </div>

    <div class="quiz-header">
        <h1 class="quiz-title">Certification Evaluation</h1>
        <div class="timer" id="timerDisplay">30:00</div>
    </div>

    <!-- UPDATE THE ACTION BELOW IF YOUR SUBMISSION FILE HAS A DIFFERENT NAME -->
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
                        ?>
                            <input type="radio" class="option-input" name="answer[<?php echo $q['question_id']; ?>]" id="q<?php echo $qNum . $opt; ?>" value="<?php echo $opt; ?>" onchange="markAnswered(<?php echo $qNum; ?>)">
                            <label class="option-label" for="q<?php echo $qNum . $opt; ?>">
                                <div class="radio-circle"></div>
                                <?php echo htmlspecialchars($optValue); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="quiz-footer">
                <button type="button" class="btn btn-outline" id="btnPrev" onclick="navigate(-1)" disabled>Previous</button>
                <button type="button" class="btn" id="btnNext" onclick="navigate(1)">Next Question</button>
                <button type="submit" class="btn" id="btnSubmit" style="display: none; background: var(--accent-success);">Submit Exam</button>
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
            
            <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 5px; font-size: 13px; color: var(--text-muted);">
                    <div style="width: 15px; height: 15px; background: var(--accent-primary); border-radius: 3px;"></div> Answered
                </div>
                <div style="display: flex; gap: 10px; align-items: center; font-size: 13px; color: var(--text-muted);">
                    <div style="width: 15px; height: 15px; background: white; border: 1px solid #e2e8f0; border-radius: 3px;"></div> Unanswered
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

        let timeLeft = <?php echo $time_limit; ?>;
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