<?php
session_start();
include '../include/db.php';

if (!isset($_SESSION['supplier_id'])) {
    header("Location: ../authentication/supplier_login.php");
    exit();
}

$supplier_id = $_SESSION['supplier_id'];

// Handle Add Question
if (isset($_POST['add_question'])) {
    $category_id = intval($_POST['category_id']);
    $question_text = mysqli_real_escape_string($conn, $_POST['question_text']);
    $option1 = mysqli_real_escape_string($conn, $_POST['option1']);
    $option2 = mysqli_real_escape_string($conn, $_POST['option2']);
    $option3 = mysqli_real_escape_string($conn, $_POST['option3']);
    $option4 = mysqli_real_escape_string($conn, $_POST['option4']);
    $correct_option = mysqli_real_escape_string($conn, $_POST['correct_option']);
    $difficulty = mysqli_real_escape_string($conn, $_POST['difficulty']);

    if (!empty($question_text) && !empty($option1) && !empty($option2) && !empty($correct_option)) {
        $insert_query = "INSERT INTO question (supplier_id, category_id, question_text, option1, option2, option3, option4, correct_option, difficulty) 
                         VALUES ('$supplier_id', '$category_id', '$question_text', '$option1', '$option2', '$option3', '$option4', '$correct_option', '$difficulty')";
        mysqli_query($conn, $insert_query);
        header("Location: manage_question.php");
        exit();
    }
}

// Handle Delete Question
if (isset($_GET['delete'])) {
    $question_id = intval($_GET['delete']);
    // Ensure supplier can only delete their own questions
    mysqli_query($conn, "DELETE FROM question WHERE question_id = $question_id AND supplier_id = $supplier_id");
    header("Location: manage_question.php");
    exit();
}

// Fetch categories for dropdown
$categories = mysqli_query($conn, "SELECT * FROM category ORDER BY category_name ASC");

// Fetch questions created by this supplier
$questions_query = "SELECT q.*, c.category_name 
                    FROM question q 
                    LEFT JOIN category c ON q.category_id = c.category_id 
                    WHERE q.supplier_id = '$supplier_id' 
                    ORDER BY q.question_id DESC";
$questions = mysqli_query($conn, $questions_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Questions - IT Quiz Creator</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body { background-color: var(--bg-main); font-family: 'Poppins', sans-serif; margin: 0; display: flex; height: 100vh; overflow: hidden; }
        
        .sidebar { width: 260px; background: white; border-right: 1px solid #e2e8f0; display: flex; flex-direction: column; padding: 30px 20px; box-sizing: border-box; }
        .sidebar-brand { font-size: 20px; font-weight: 700; color: var(--text-dark); margin-bottom: 40px; }
        .sidebar-menu { display: flex; flex-direction: column; gap: 10px; flex: 1; }
        .sidebar-link { padding: 12px 16px; border-radius: var(--radius-md); text-decoration: none; color: var(--text-muted); font-weight: 500; font-size: 15px; transition: var(--transition); }
        .sidebar-link:hover, .sidebar-link.active { background: #eff6ff; color: var(--accent-primary); }
        .sidebar-logout { padding: 12px 16px; border-radius: var(--radius-md); text-decoration: none; color: #ef4444; font-weight: 500; font-size: 15px; transition: var(--transition); }
        .sidebar-logout:hover { background: #fee2e2; }

        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; padding: 40px; box-sizing: border-box; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; }
        .page-header h1 { font-size: 26px; color: var(--text-dark); margin: 0; }

        .content-grid { display: grid; grid-template-columns: 1fr 1.5fr; gap: 30px; align-items: start; }
        
        .form-card, .table-card { background: white; border-radius: var(--radius-lg); border: 1px solid #e2e8f0; box-shadow: var(--shadow-sm); padding: 30px; box-sizing: border-box; }
        .form-card h2, .table-card h2 { margin-top: 0; font-size: 18px; color: var(--text-dark); margin-bottom: 20px; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 13px; font-weight: 500; color: var(--text-dark); margin-bottom: 6px; }
        .form-control { width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: var(--radius-md); font-size: 14px; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        .form-control:focus { outline: none; border-color: var(--accent-primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }

        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
        th { padding: 12px; background: #f8fafc; color: var(--text-muted); font-weight: 600; border-bottom: 2px solid #e2e8f0; }
        td { padding: 12px; border-bottom: 1px solid #e2e8f0; color: var(--text-dark); }
        tr:hover td { background: #f8fafc; }
        
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; background: #eff6ff; color: var(--accent-primary); }
        .btn-danger { background: #fee2e2; color: #dc2626; padding: 6px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; transition: var(--transition); display: inline-block; }
        .btn-danger:hover { background: #fca5a5; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-brand">IT Quiz Creator</div>
        <div class="sidebar-menu">
            <a href="dashboard.php" class="sidebar-link">Dashboard</a>
            <a href="manage_question.php" class="sidebar-link active">Manage Questions</a>
            <a href="profile.php" class="sidebar-link">Profile</a>
        </div>
        <a href="logout.php" class="sidebar-logout">Logout</a>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1>Question Bank Management</h1>
        </div>

        <div class="content-grid">
            <!-- Add Question Form -->
            <div class="form-card">
                <h2>Add New Question</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                                <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Difficulty</label>
                        <select name="difficulty" class="form-control" required>
                            <option value="Easy">Easy</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="Hard">Hard</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Question Text</label>
                        <textarea name="question_text" class="form-control" rows="3" placeholder="Enter question description..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label>Option A</label>
                        <input type="text" name="option1" class="form-control" placeholder="First option" required>
                    </div>

                    <div class="form-group">
                        <label>Option B</label>
                        <input type="text" name="option2" class="form-control" placeholder="Second option" required>
                    </div>

                    <div class="form-group">
                        <label>Option C</label>
                        <input type="text" name="option3" class="form-control" placeholder="Third option">
                    </div>

                    <div class="form-group">
                        <label>Option D</label>
                        <input type="text" name="option4" class="form-control" placeholder="Fourth option">
                    </div>

                    <div class="form-group">
                        <label>Correct Answer</label>
                        <input type="text" name="correct_option" class="form-control" placeholder="Must match one option exactly" required>
                    </div>

                    <button type="submit" name="add_question" class="btn" style="width: 100%; margin-top: 10px;">Save Question</button>
                </form>
            </div>

            <!-- Questions Table -->
            <div class="table-card">
                <h2>Your Question Bank</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Category</th>
                            <th>Question</th>
                            <th>Difficulty</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($questions) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($questions)): ?>
                                <tr>
                                    <td>#<?php echo $row['question_id']; ?></td>
                                    <td><span class="badge"><?php echo htmlspecialchars($row['category_name'] ?? 'General'); ?></span></td>
                                    <td><?php echo htmlspecialchars(substr($row['question_text'] ?? '', 0, 50)) . '...'; ?></td>
                                    <td><?php echo htmlspecialchars($row['difficulty'] ?? 'Medium'); ?></td>
                                    <td>
                                        <a href="manage_question.php?delete=<?php echo $row['question_id']; ?>" class="btn-danger" onclick="return confirm('Are you sure you want to delete this question?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 30px;">No questions created yet. Use the form on the left to add your first question.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>