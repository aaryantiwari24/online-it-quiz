<?php
session_start(); // This allows the homepage to read your login status!
include 'include/db.php';

// Fetch REAL Statistics from your database
$q_count = mysqli_query($conn, "SELECT COUNT(*) as total FROM question");
$total_questions = mysqli_fetch_assoc($q_count)['total'] ?? 0;

$a_count = mysqli_query($conn, "SELECT COUNT(*) as total FROM result");
$total_attempts = mysqli_fetch_assoc($a_count)['total'] ?? 0;

$c_count = mysqli_query($conn, "SELECT COUNT(*) as total FROM result WHERE status = 'Pass'");
$total_certificates = mysqli_fetch_assoc($c_count)['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Quiz | Professional Certification</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .navbar { display: flex; justify-content: space-between; align-items: center; padding: 20px 5%; background: var(--bg-card); position: sticky; top: 0; z-index: 100; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .logo { font-size: 22px; font-weight: 700; color: var(--accent-primary); text-decoration: none; display: flex; align-items: center; gap: 8px; }
        .hero { display: flex; align-items: center; justify-content: space-between; padding: 100px 5%; min-height: 80vh; gap: 50px; }
        .hero-text { flex: 1; }
        .hero-text h1 { font-size: 55px; line-height: 1.1; color: var(--text-dark); }
        .hero-text p { font-size: 18px; margin-bottom: 30px; max-width: 500px; }
        .hero-visual { flex: 1; position: relative; height: 400px; }
        .floating-card { position: absolute; background: white; padding: 20px; border-radius: 15px; box-shadow: var(--shadow-hover); animation: float 6s ease-in-out infinite; }
        .fc-1 { top: 10%; left: 10%; z-index: 2; animation-delay: 0s; }
        .fc-2 { top: 40%; right: 10%; z-index: 1; animation-delay: 2s; border-left: 4px solid var(--accent-success); }
        .fc-3 { bottom: 0; left: 30%; z-index: 3; animation-delay: 4s; border-left: 4px solid var(--accent-primary); }
        @keyframes float { 0% { transform: translateY(0px); } 50% { transform: translateY(-20px); } 100% { transform: translateY(0px); } }
        .stats-section { display: flex; justify-content: center; gap: 40px; padding: 60px 5%; background: white; text-align: center; }
        .stat-item h2 { font-size: 40px; color: var(--accent-primary); margin: 0; }
        .stat-item p { margin: 0; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; font-weight: 500; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="logo">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="var(--accent-primary)"><path d="M12 2L2 7l10 5 10-5-10-5zm0 7.5l-10-5v10l10 5 10-5v-10l-10 5z"/></svg>
            IT Quiz Platform
        </a>
        <div class="nav-links">
            <!-- Dynamic Buttons Based on Login Status -->
            <?php if (isset($_SESSION['customer_id'])): ?>
                <a href="customer/dashboard.php" class="btn">My Dashboard</a>
            <?php elseif (isset($_SESSION['supplier_id'])): ?>
                <a href="supplier/dashboard.php" class="btn">Supplier Portal</a>
            <?php elseif (isset($_SESSION['admin'])): ?>
                <a href="admin/dashboard.php" class="btn">Admin Panel</a>
            <?php else: ?>
                <a href="authentication/customer_login.php" class="btn btn-outline" style="margin-right: 15px;">Login</a>
                <a href="authentication/customer_register.php" class="btn">Start Learning</a>
            <?php endif; ?>
        </div>
    </nav>

    <header class="hero">
        <div class="hero-text reveal">
            <h1>Test Your IT Knowledge.<br>Prove Your Skills.</h1>
            <p>Join the premier professional certification platform. Take dynamically generated quizzes, track your progress, and earn verified certificates.</p>
            <div style="display: flex; gap: 15px;">
                <?php if (!isset($_SESSION['customer_id']) && !isset($_SESSION['supplier_id']) && !isset($_SESSION['admin'])): ?>
                    <a href="authentication/customer_register.php" class="btn">Start Quiz</a>
                    <a href="authentication/supplier_login.php" class="btn btn-outline">Login As Supplier</a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="hero-visual reveal">
            <div class="floating-card fc-1">
                <strong style="color: var(--accent-primary);">JavaScript Functions</strong>
                <p style="margin:5px 0 0; font-size: 13px;">Time Remaining: 12:45</p>
            </div>
            <div class="floating-card fc-2">
                <strong style="color: var(--accent-success);">Certificate Earned!</strong>
                <p style="margin:5px 0 0; font-size: 13px;">Score: 92% | Pass</p>
            </div>
            <div class="floating-card fc-3">
                <strong style="color: var(--text-dark);">Cyber Security</strong>
                <p style="margin:5px 0 0; font-size: 13px;">Difficulty: Hard</p>
            </div>
        </div>
    </header>

    <section class="stats-section reveal">
        <div class="stat-item">
            <h2 class="counter" data-target="<?php echo $total_questions; ?>">0</h2>
            <p>Live Questions</p>
        </div>
        <div class="stat-item">
            <h2 class="counter" data-target="<?php echo $total_attempts; ?>">0</h2>
            <p>Quizzes Taken</p>
        </div>
        <div class="stat-item">
            <h2 class="counter" data-target="<?php echo $total_certificates; ?>">0</h2>
            <p>Certificates Earned</p>
        </div>
    </section>

    <script>
        function reveal() {
            var reveals = document.querySelectorAll(".reveal");
            for (var i = 0; i < reveals.length; i++) {
                var windowHeight = window.innerHeight;
                var elementTop = reveals[i].getBoundingClientRect().top;
                if (elementTop < windowHeight - 100) reveals[i].classList.add("active");
            }
        }
        window.addEventListener("scroll", reveal);
        reveal(); 

        const counters = document.querySelectorAll('.counter');
        counters.forEach(counter => {
            const updateCount = () => {
                const target = +counter.getAttribute('data-target');
                const count = +counter.innerText;
                const inc = target / 50; 

                if (count < target) {
                    counter.innerText = Math.ceil(count + inc);
                    setTimeout(updateCount, 30);
                } else {
                    counter.innerText = target;
                }
            };
            
            let observer = new IntersectionObserver(function(entries) {
                if(entries[0].isIntersecting === true) updateCount();
            }, { threshold: [0] });
            
            observer.observe(counter);
        });
    </script>
</body>
</html>