<?php
session_start();
include '../include/db.php';

// Prevent browser form resubmission warning
header("Cache-Control: private, must-revalidate, max-age=0");

if (!isset($_SESSION['supplier_id'])) {
    header("Location: ../authentication/supplier_login.php");
    exit();
}

$supplier_id = $_SESSION['supplier_id'];
$success_msg = "";
$error_msg = "";

// ==========================================
// 1. HANDLE ADD QUESTION
// ==========================================
if (isset($_POST['add_question'])) {
    $cat_id = intval($_POST['category_id']);
    $diff = mysqli_real_escape_string($conn, $_POST['difficulty']);
    $q_text = mysqli_real_escape_string($conn, $_POST['question']);
    $opt_a = mysqli_real_escape_string($conn, $_POST['option_a']);
    $opt_b = mysqli_real_escape_string($conn, $_POST['option_b']);
    $opt_c = mysqli_real_escape_string($conn, $_POST['option_c']);
    $opt_d = mysqli_real_escape_string($conn, $_POST['option_d']);
    $correct = mysqli_real_escape_string($conn, $_POST['correct_answer']);

    $query = "INSERT INTO question (category_id, supplier_id, question, option_a, option_b, option_c, option_d, correct_answer, difficulty) 
              VALUES ($cat_id, $supplier_id, '$q_text', '$opt_a', '$opt_b', '$opt_c', '$opt_d', '$correct', '$diff')";
    
    if (mysqli_query($conn, $query)) {
        $success_msg = "Question added successfully!";
    } else {
        $error_msg = "Error adding question: " . mysqli_error($conn);
    }
}

// ==========================================
// 2. HANDLE UPDATE QUESTION
// ==========================================
if (isset($_POST['update_question'])) {
    $q_id = intval($_POST['question_id']);
    $cat_id = intval($_POST['category_id']);
    $diff = mysqli_real_escape_string($conn, $_POST['difficulty']);
    $q_text = mysqli_real_escape_string($conn, $_POST['question']);
    $opt_a = mysqli_real_escape_string($conn, $_POST['option_a']);
    $opt_b = mysqli_real_escape_string($conn, $_POST['option_b']);
    $opt_c = mysqli_real_escape_string($conn, $_POST['option_c']);
    $opt_d = mysqli_real_escape_string($conn, $_POST['option_d']);
    $correct = mysqli_real_escape_string($conn, $_POST['correct_answer']);

    $query = "UPDATE question SET 
              category_id = $cat_id, question = '$q_text', 
              option_a = '$opt_a', option_b = '$opt_b', option_c = '$opt_c', option_d = '$opt_d', 
              correct_answer = '$correct', difficulty = '$diff' 
              WHERE question_id = $q_id AND supplier_id = $supplier_id";
    
    if (mysqli_query($conn, $query)) {
        $success_msg = "Question updated successfully!";
    } else {
        $error_msg = "Error updating question: " . mysqli_error($conn);
    }
}

// ==========================================
// 3. HANDLE DELETE QUESTION
// ==========================================
if (isset($_POST['delete_question'])) {
    $q_id = intval($_POST['delete_id']);
    $query = "DELETE FROM question WHERE question_id = $q_id AND supplier_id = $supplier_id";
    
    if (mysqli_query($conn, $query)) {
        $success_msg = "Question deleted successfully!";
    } else {
        $error_msg = "Error deleting question: " . mysqli_error($conn);
    }
}

// Fetch Categories for dropdowns
$categories = [];
$cat_query = mysqli_query($conn, "SELECT * FROM category ORDER BY category_name ASC");
while ($row = mysqli_fetch_assoc($cat_query)) { $categories[] = $row; }

// Fetch All Questions for this supplier
$all_q_query = "SELECT q.*, c.category_name FROM question q 
                LEFT JOIN category c ON q.category_id = c.category_id 
                WHERE q.supplier_id = $supplier_id ORDER BY q.question_id DESC";
$all_questions = mysqli_query($conn, $all_q_query);
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Questions - Instructor Portal</title>
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

        .action-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        
        /* Tables */
        .table-card { background: var(--surface-white); border-radius: var(--radius-lg); border: 1px solid var(--border-light); box-shadow: var(--shadow-subtle); padding: 30px; }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
        th { padding: 14px 18px; background: rgba(104, 112, 137, 0.04); color: var(--text-secondary); font-weight: 700; border-bottom: 1px solid var(--border-light); text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }
        td { padding: 16px 18px; border-bottom: 1px solid var(--border-light); color: var(--text-primary); }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(104, 112, 137, 0.02); }
        .badge { padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; background: rgba(81, 70, 229, 0.1); color: var(--brand-primary); }

        /* Forms / Modals */
        .form-card { background: var(--surface-white); border-radius: var(--radius-lg); border: 1px solid var(--border-light); box-shadow: var(--shadow-subtle); padding: 30px; margin-bottom: 30px; display: none; }
        .form-card.active { display: block; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-control { width: 100%; padding: 12px 16px; border: 1.5px solid var(--border-light); background: var(--bg-main); color: var(--text-primary); border-radius: var(--radius-md); font-size: 15px; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; transition: var(--transition); }
        .form-control:focus { outline: none; border-color: var(--brand-primary); }
        textarea.form-control { resize: vertical; min-height: 100px; }
        
        .btn-custom { padding: 10px 20px; border-radius: var(--radius-md); font-weight: 600; font-size: 14px; text-decoration: none; cursor: pointer; transition: var(--transition); border: none; display: inline-block; }
        .btn-primary { background: var(--brand-primary); color: white; }
        .btn-primary:hover { background: var(--brand-secondary); }
        .btn-danger { background: rgba(255, 107, 74, 0.1); color: var(--accent-coral); border: 1px solid rgba(255, 107, 74, 0.3); padding: 6px 14px; font-size: 12px; }
        .btn-danger:hover { background: var(--accent-coral); color: white; }
        .btn-edit { background: rgba(81, 70, 229, 0.1); color: var(--brand-primary); border: 1px solid rgba(81, 70, 229, 0.3); padding: 6px 14px; font-size: 12px; }
        .btn-edit:hover { background: var(--brand-primary); color: white; }

        .alert { padding: 15px; border-radius: var(--radius-md); font-weight: 600; font-size: 14px; margin-bottom: 25px; }
        .alert-success { background: rgba(24, 183, 122, 0.1); border: 1px solid rgba(24, 183, 122, 0.2); color: var(--accent-green); }
        .alert-error { background: rgba(255, 107, 74, 0.1); border: 1px solid rgba(255, 107, 74, 0.2); color: var(--accent-coral); }
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
            <a href="dashboard.php" class="sidebar-link">📊 Dashboard</a>
            <a href="manage_question.php" class="sidebar-link active">🧩 Manage Questions</a>
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
            <h1>Manage Question Bank</h1>
        </div>

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-error"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <div class="action-bar">
            <h2 style="font-size: 18px; font-weight: 800; color: var(--text-primary); margin:0;">Your Questions</h2>
            <button type="button" class="btn-custom btn-primary" onclick="toggleAddForm()" id="addToggleBtn">+ Add New Question</button>
        </div>

        <!-- ADD QUESTION FORM (Hidden by default) -->
        <div class="form-card" id="addFormCard">
            <h2 style="margin-top: 0; margin-bottom: 20px; font-size: 18px;">Create New Question</h2>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Difficulty Tier</label>
                        <select name="difficulty" class="form-control" required>
                            <option value="Easy">Tier 1 - Easy</option>
                            <option value="Medium">Tier 2 - Medium</option>
                            <option value="Hard">Tier 3 - Hard</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Question Text</label>
                    <textarea name="question" class="form-control" required placeholder="Enter question..."></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Option A</label>
                        <input type="text" name="option_a" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Option B</label>
                        <input type="text" name="option_b" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Option C</label>
                        <input type="text" name="option_c" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Option D</label>
                        <input type="text" name="option_d" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Correct Answer</label>
                    <select name="correct_answer" class="form-control" required>
                        <option value="a">Option A</option>
                        <option value="b">Option B</option>
                        <option value="c">Option C</option>
                        <option value="d">Option D</option>
                    </select>
                </div>

                <button type="submit" name="add_question" class="btn-custom btn-primary">Save Question</button>
            </form>
        </div>

        <!-- UPDATE QUESTION FORM (Hidden by default, opened when clicking Edit) -->
        <div class="form-card" id="editFormCard">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="margin: 0; font-size: 18px;">Update Question</h2>
                <button type="button" class="btn-custom btn-danger" onclick="closeEditForm()">Cancel</button>
            </div>
            
            <form method="POST">
                <input type="hidden" name="question_id" id="edit_q_id">
                <div class="form-row">
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category_id" id="edit_category" class="form-control" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Difficulty Tier</label>
                        <select name="difficulty" id="edit_diff" class="form-control" required>
                            <option value="Easy">Tier 1 - Easy</option>
                            <option value="Medium">Tier 2 - Medium</option>
                            <option value="Hard">Tier 3 - Hard</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Question Text</label>
                    <textarea name="question" id="edit_q_text" class="form-control" required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Option A</label>
                        <input type="text" name="option_a" id="edit_opt_a" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Option B</label>
                        <input type="text" name="option_b" id="edit_opt_b" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Option C</label>
                        <input type="text" name="option_c" id="edit_opt_c" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Option D</label>
                        <input type="text" name="option_d" id="edit_opt_d" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Correct Answer</label>
                    <select name="correct_answer" id="edit_correct" class="form-control" required>
                        <option value="a">Option A</option>
                        <option value="b">Option B</option>
                        <option value="c">Option C</option>
                        <option value="d">Option D</option>
                    </select>
                </div>

                <button type="submit" name="update_question" class="btn-custom btn-primary">Update Question</button>
            </form>
        </div>

        <!-- QUESTIONS LIST TABLE -->
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Question</th>
                        <th>Difficulty</th>
                        <th>Answer</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($all_questions) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($all_questions)): ?>
                            <tr>
                                <td><span class="badge"><?php echo htmlspecialchars($row['category_name'] ?? ''); ?></span></td>
                                <td style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?php echo htmlspecialchars($row['question'] ?? ''); ?>
                                </td>
                                <td><strong><?php echo htmlspecialchars(ucfirst($row['difficulty'])); ?></strong></td>
                                <td style="text-transform: uppercase; font-weight: bold;"><?php echo htmlspecialchars($row['correct_answer']); ?></td>
                                <td style="text-align: right; display: flex; gap: 8px; justify-content: flex-end;">
                                    <button type="button" class="btn-custom btn-edit" onclick='openEditForm(<?php echo json_encode($row); ?>)'>Edit</button>
                                    
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this question permanently?');">
                                        <input type="hidden" name="delete_id" value="<?php echo $row['question_id']; ?>">
                                        <button type="submit" name="delete_question" class="btn-custom btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-secondary); padding: 40px;">No questions found. Click "+ Add New Question" to begin.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Dark Mode Logic
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

        // Toggle Add Form visibility
        function toggleAddForm() {
            const addCard = document.getElementById('addFormCard');
            const editCard = document.getElementById('editFormCard');
            editCard.classList.remove('active');
            addCard.classList.toggle('active');
        }

        // Open Edit Form and populate values
        function openEditForm(data) {
            document.getElementById('addFormCard').classList.remove('active');
            document.getElementById('editFormCard').classList.add('active');
            
            document.getElementById('edit_q_id').value = data.question_id;
            document.getElementById('edit_category').value = data.category_id;
            document.getElementById('edit_diff').value = data.difficulty;
            document.getElementById('edit_q_text').value = data.question;
            document.getElementById('edit_opt_a').value = data.option_a;
            document.getElementById('edit_opt_b').value = data.option_b;
            document.getElementById('edit_opt_c').value = data.option_c;
            document.getElementById('edit_opt_d').value = data.option_d;
            document.getElementById('edit_correct').value = data.correct_answer;
            
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function closeEditForm() {
            document.getElementById('editFormCard').classList.remove('active');
        }
    </script>
</body>
</html>