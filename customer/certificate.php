<?php
session_start();
include '../include/db.php';

if (!isset($_SESSION['customer_id']) || !isset($_GET['result_id'])) {
    header("Location: dashboard.php");
    exit();
}

$result_id = intval($_GET['result_id']);
$customer_id = $_SESSION['customer_id'];

// Fetch result, customer name, category name, and difficulty
$query = "SELECT r.*, c.category_name, cu.name as customer_name 
          FROM result r 
          JOIN customer cu ON r.customer_id = cu.customer_id 
          LEFT JOIN category c ON r.category_id = c.category_id 
          WHERE r.result_id = $result_id AND r.customer_id = $customer_id";

$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if (!$data || $data['status'] != 'Pass') {
    die("Unauthorized access or certificate not available.");
}

$student_name = $data['customer_name'];
$category_name = $data['category_name'] ?? 'IT Certification';
$difficulty = !empty($data['difficulty']) ? $data['difficulty'] : 'General';
$percentage = $data['percentage'];
$date_earned = date('F d, Y', strtotime($data['attempt_date']));
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Completion - IT Quiz</title>
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
        
        .certificate-wrapper { 
            background: var(--surface-white); width: 900px; padding: 60px; 
            border-radius: var(--radius-lg); box-shadow: var(--shadow-hover); 
            border: 8px solid var(--brand-primary); position: relative; 
            text-align: center; box-sizing: border-box; transition: var(--transition);
        }
        .cert-header { font-size: 32px; font-weight: 800; color: var(--text-primary); letter-spacing: 2px; text-transform: uppercase; margin-bottom: 5px; }
        .cert-subheader { font-size: 13px; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 4px; margin-bottom: 40px; font-weight: 700; }
        .cert-body { font-size: 16px; color: var(--text-secondary); margin-bottom: 20px; font-weight: 500; }
        .student-name { font-size: 40px; font-weight: 800; color: var(--text-primary); border-bottom: 2px solid var(--border-light); display: inline-block; padding-bottom: 8px; margin: 10px 0 35px 0; min-width: 450px; }
        .cert-description { font-size: 16px; color: var(--text-primary); line-height: 1.6; max-width: 700px; margin: 0 auto 50px auto; font-weight: 500; }
        .cert-footer { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 40px; padding: 0 40px; }
        .signature-block { text-align: center; width: 220px; }
        .signature-line { border-top: 1.5px solid var(--text-primary); margin-bottom: 8px; }
        .signature-title { font-size: 12px; color: var(--text-secondary); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .signature-name { font-size: 14px; font-weight: 700; color: var(--text-primary); }
        
        .action-bar { position: fixed; top: 20px; right: 20px; display: flex; gap: 12px; z-index: 100; }
        .btn-custom { padding: 12px 24px; border-radius: var(--radius-md); font-weight: 600; font-size: 14px; text-decoration: none; cursor: pointer; transition: var(--transition); border: none; display: inline-flex; align-items: center; gap: 8px; }
        .btn-custom.primary { background: var(--brand-primary); color: white; box-shadow: 0 4px 12px rgba(81, 70, 229, 0.2); }
        .btn-custom.primary:hover { background: var(--brand-secondary); transform: translateY(-1px); }
        .btn-custom.outline { background: var(--surface-white); border: 1.5px solid var(--border-light); color: var(--text-primary); }
        .btn-custom.outline:hover { border-color: var(--text-primary); }

        @media print {
            body { background: white; height: auto; }
            .action-bar { display: none; }
            .certificate-wrapper { box-shadow: none; border: 6px solid #15182B; width: 100%; padding: 40px; }
        }
    </style>
</head>
<body>

    <div class="action-bar">
        <button onclick="window.print()" class="btn-custom primary">Print / Save PDF</button>
        <a href="dashboard.php" class="btn-custom outline">Back to Dashboard</a>
    </div>

    <div class="certificate-wrapper">
        <div class="cert-header">Certificate of Completion</div>
        <div class="cert-subheader">Verified Professional Credential</div>

        <div class="cert-body">This is proudly presented to</div>
        
        <div>
            <span class="student-name"><?php echo htmlspecialchars($student_name); ?></span>
        </div>

        <div class="cert-description">
            for successfully passing the official 
            <strong style="color: var(--brand-primary);"><?php echo htmlspecialchars($difficulty); ?></strong> 
            certification evaluation in 
            <strong style="color: var(--brand-primary);"><?php echo htmlspecialchars($category_name); ?></strong> 
            with a score of <strong style="color: var(--accent-green);"><?php echo $percentage; ?>%</strong>.
        </div>

        <div class="cert-footer">
            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-name">Date Earned</div>
                <div class="signature-title"><?php echo $date_earned; ?></div>
            </div>

            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-name">IT Quiz System</div>
                <div class="signature-title">Authorized Signature</div>
            </div>
        </div>
    </div>

</body>
</html>