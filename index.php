<?php
session_start(); 
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
        
        /* Navigation Styles */
        .nav-links { display: flex; align-items: center; gap: 25px; }
        .nav-text { text-decoration: none; color: var(--text-dark); font-weight: 500; font-size: 15px; transition: color 0.3s ease; }
        .nav-text:hover { color: var(--accent-primary); }

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
        
        /* Content Sections */
        .content-section { padding: 80px 5%; background: var(--bg-main); text-align: center; }
        .content-section.white-bg { background: white; }
        .section-title { font-size: 36px; color: var(--text-dark); margin-bottom: 15px; }
        .section-lead { max-width: 700px; margin: 0 auto 50px; color: var(--text-muted); line-height: 1.6; font-size: 16px; }

        /* Stats Grid */
        .stats-grid { display: flex; justify-content: center; gap: 40px; flex-wrap: wrap; }
        .stat-item h2 { font-size: 40px; color: var(--accent-primary); margin: 0; }
        .stat-item p { margin: 0; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; color: var(--text-dark); }

        /* FAQ Grid */
        .faq-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; max-width: 1000px; margin: 0 auto; text-align: left; }
        .faq-card { background: white; padding: 30px; border-radius: 12px; box-shadow: var(--shadow-sm); border: 1px solid #e2e8f0; transition: transform 0.3s ease; }
        .faq-card:hover { transform: translateY(-5px); }
        .faq-card h3 { margin-top: 0; color: var(--accent-primary); font-size: 18px; margin-bottom: 12px; }
        .faq-card p { margin: 0; color: var(--text-muted); font-size: 14px; line-height: 1.6; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="logo">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="var(--accent-primary)"><path d="M12 2L2 7l10 5 10-5-10-5zm0 7.5l-10-5v10l10 5 10-5v-10l-10 5z"/></svg>
            IT Quiz Platform
        </a>
        <div class="nav-links">
            <a href="#about" class="nav-text">About Us</a>
            <a href="#faq" class="nav-text">FAQ</a>

            <?php if (isset($_SESSION['customer_id'])): ?>
                <a href="customer/dashboard.php" class="btn">My Dashboard</a>
            <?php elseif (isset($_SESSION['supplier_id'])): ?>
                <a href="supplier/dashboard.php" class="btn">Supplier Portal</a>
            <?php elseif (isset($_SESSION['admin'])): ?>
                <a href="admin/dashboard.php" class="btn">Admin Panel</a>
            <?php else: ?>
                <a href="authentication/customer_login.php" class="nav-text">Login</a>
                <a href="authentication/supplier_login.php" class="nav-text">Supplier Login</a>
                <a href="authentication/admin_login.php" class="btn">Admin Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <header class="hero">
        <div class="hero-text reveal">
            <h1>Test Your IT Knowledge.<br>Prove Your Skills.</h1>
            <p>Join the premier professional certification platform. Take dynamically generated quizzes, track your progress, and earn verified certificates.</p>
            <div style="display: flex; gap: 15px;">
                <?php if (!isset($_SESSION['customer_id']) && !isset($_SESSION['supplier_id']) && !isset($_SESSION['admin'])): ?>
                    <a href="authentication/customer_register.php" class="btn">Register to Start</a>
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

    <!-- About Us Section -->
    <section id="about" class="content-section white-bg reveal">
        <h2 class="section-title">About the Platform</h2>
        <p class="section-lead">Our mission is to bridge the gap between learning and professional validation. We provide a robust testing environment where IT students and professionals can evaluate their skills across multiple disciplines and earn tangible proof of their expertise.</p>
        
        <div class="stats-grid">
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
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="content-section reveal">
        <h2 class="section-title">Frequently Asked Questions</h2>
        <p class="section-lead">Find answers to the most common questions about taking quizzes, earning certificates, and contributing to the platform.</p>
        
        <div class="faq-grid">
            <div class="faq-card">
                <h3>How do I earn a certificate?</h3>
                <p>Simply register as a student, select a category, and pass the quiz. If your score meets the passing threshold, a verified digital certificate is automatically generated for you to download and share.</p>
            </div>
            <div class="faq-card">
                <h3>Is the platform free to use?</h3>
                <p>Yes! Creating a student account and taking standard certification quizzes is entirely free. We believe in accessible skill validation for everyone.</p>
            </div>
            <div class="faq-card">
                <h3>Who creates the quiz questions?</h3>
                <p>Our questions are submitted by industry professionals and instructors through our Supplier Portal. They are categorized and ranked by difficulty to ensure high-quality testing.</p>
            </div>
            <div class="faq-card">
                <h3>Can I retake a failed quiz?</h3>
                <p>Yes, you can retake quizzes. However, our system dynamically pulls random questions from the database each time, so you will likely see a different set of questions on your next attempt.</p>
            </div>
        </div>
    </section>

    <script>
        // Scroll Reveal Logic
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

        // Number Counter Logic
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