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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Completion - IT Quiz</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body { background-color: #e2e8f0; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; font-family: 'Poppins', sans-serif; }
        .certificate-wrapper { background: white; width: 850px; padding: 60px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); border: 10px solid var(--accent-primary); position: relative; text-align: center; box-sizing: border-box; }
        .cert-header { font-size: 32px; font-weight: 700; color: var(--text-dark); letter-spacing: 2px; text-transform: uppercase; margin-bottom: 5px; }
        .cert-subheader { font-size: 14px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 4px; margin-bottom: 40px; }
        .cert-body { font-size: 16px; color: var(--text-muted); margin-bottom: 20px; }
        .student-name { font-size: 38px; font-weight: 700; color: var(--text-dark); border-bottom: 2px solid #cbd5e1; display: inline-block; padding-bottom: 5px; margin: 10px 0 30px 0; min-width: 400px; }
        .cert-description { font-size: 16px; color: var(--text-dark); line-height: 1.6; max-width: 650px; margin: 0 auto 50px auto; }
        .cert-footer { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 40px; padding: 0 40px; }
        .signature-block { text-align: center; width: 200px; }
        .signature-line { border-top: 1px solid var(--text-dark); margin-bottom: 8px; }
        .signature-title { font-size: 13px; color: var(--text-muted); font-weight: 500; }
        .signature-name { font-size: 14px; font-weight: 600; color: var(--text-dark); }
        .action-bar { position: fixed; top: 20px; right: 20px; display: flex; gap: 10px; }
        @media print {
            body { background: white; height: auto; }
            .action-bar { display: none; }
            .certificate-wrapper { box-shadow: none; border: 8px solid #0f172a; width: 100%; }
        }
    </style>
</head>
<body>

    <div class="action-bar">
        <button onclick="window.print()" class="btn">Print / Save PDF</button>
        <a href="dashboard.php" class="btn btn-outline" style="background: white;">Back to Dashboard</a>
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
            <strong style="color: var(--accent-primary);"><?php echo htmlspecialchars($difficulty); ?></strong> 
            certification evaluation in 
            <strong style="color: var(--accent-primary);"><?php echo htmlspecialchars($category_name); ?></strong> 
            with a score of <strong style="color: var(--accent-success);"><?php echo $percentage; ?>%</strong>.
        </div>

        <div class="cert-footer">
            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-name">Date Earned</div>
                <div class="signature-title"><?php echo $date_earned; ?></div>
            </div>

            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-name">IT Quiz Admin</div>
                <div class="signature-title">Authorized Signature</div>
            </div>
        </div>
    </div>

</body>
</html>