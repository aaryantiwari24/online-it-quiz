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
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Quiz | Professional Certification</title>
    <!-- Plus Jakarta Sans for a modern, friendly, and premium tech aesthetic -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    
    <script>
        // Immediately apply saved theme before body renders to prevent flash
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>

    <style>
        /* =========================================================
           PAGE-SPECIFIC STYLES & LAYOUT SYSTEM
           ========================================================= */
        .navbar { 
            display: flex; justify-content: space-between; align-items: center; 
            padding: 20px 5%; background: var(--bg-main); 
            backdrop-filter: blur(12px); position: sticky; top: 0; z-index: 100;
            border-bottom: 1px solid var(--border-light);
            transition: background 0.3s ease, border 0.3s ease;
        }
        .logo { 
            font-size: 20px; font-weight: 800; color: var(--text-primary); 
            text-decoration: none; display: flex; align-items: center; gap: 10px; letter-spacing: -0.5px; 
        }
        .logo-icon {
            width: 32px; height: 32px; background: var(--brand-primary); 
            border-radius: 8px; display: flex; align-items: center; justify-content: center;
        }
        
        .nav-links { display: flex; align-items: center; gap: 24px; }
        .nav-text { 
            text-decoration: none; color: var(--text-secondary); font-weight: 500; 
            font-size: 15px; transition: var(--transition);
        }
        .nav-text:hover { color: var(--brand-primary); }
        
        .btn-subtle { 
            text-decoration: none; color: var(--text-secondary); font-weight: 600; 
            font-size: 14px; padding: 10px 16px; border-radius: var(--radius-md); transition: var(--transition);
        }
        .btn-subtle:hover { background: var(--border-light); color: var(--text-primary); }

        .btn-logout-nav {
            text-decoration: none; color: var(--accent-coral); font-weight: 600; 
            font-size: 14px; padding: 10px 16px; border-radius: var(--radius-md); 
            background: rgba(255, 107, 74, 0.1); border: 1px solid rgba(255, 107, 74, 0.2);
            transition: var(--transition);
        }
        .btn-logout-nav:hover { background: var(--accent-coral); color: white; }
        
        .btn-primary { 
            background: var(--brand-primary); color: #FFFFFF; padding: 12px 24px; 
            text-decoration: none; border-radius: var(--radius-md); font-weight: 600; 
            font-size: 15px; display: inline-flex; align-items: center; gap: 8px; 
            transition: var(--transition); box-shadow: 0 4px 12px rgba(81, 70, 229, 0.2);
        }
        .btn-primary .arrow { transition: transform 0.3s ease; font-weight: bold; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(81, 70, 229, 0.3); }
        .btn-primary:hover .arrow { transform: translate(4px, -4px); }

        .theme-toggle {
            background: none; border: 1px solid var(--border-light);
            width: 40px; height: 40px; border-radius: 50%; display: flex;
            align-items: center; justify-content: center; cursor: pointer;
            color: var(--text-primary); transition: var(--transition);
        }
        .theme-toggle:hover { background: var(--border-light); }

        .hero { 
            display: grid; grid-template-columns: 1fr 1fr; gap: 50px; 
            padding: 80px 5% 100px; min-height: 85vh; align-items: center; 
        }
        .hero-content { max-width: 540px; }
        .eyebrow { 
            display: inline-block; padding: 6px 14px; background: rgba(81, 70, 229, 0.1); 
            color: var(--brand-primary); font-size: 13px; font-weight: 700; 
            border-radius: 30px; margin-bottom: 24px; letter-spacing: 0.5px; text-transform: uppercase;
        }
        .hero-title { 
            font-size: 56px; font-weight: 800; line-height: 1.15; 
            color: var(--text-primary); margin-bottom: 24px; letter-spacing: -1px; 
        }
        .hero-title .highlight { color: var(--brand-primary); }
        .hero-desc { font-size: 18px; color: var(--text-secondary); line-height: 1.6; margin-bottom: 40px; }
        .hero-actions { display: flex; align-items: center; gap: 20px; }

        .hero-visual { position: relative; height: 500px; display: flex; justify-content: center; align-items: center; }
        .visual-path { position: absolute; width: 100%; height: 100%; z-index: 0; pointer-events: none; }
        
        .quiz-card { 
            position: relative; z-index: 2; background: var(--surface-white); 
            padding: 30px; border-radius: var(--radius-lg); box-shadow: var(--shadow-subtle); 
            border: 1px solid var(--border-light); width: 340px; animation: float 6s ease-in-out infinite; 
        }
        .qc-header { display: flex; justify-content: space-between; font-size: 13px; font-weight: 700; color: var(--text-secondary); margin-bottom: 20px; }
        .qc-question { font-size: 18px; font-weight: 700; color: var(--text-primary); margin-bottom: 20px; line-height: 1.4; }
        .qc-option { 
            padding: 12px 16px; border: 1.5px solid var(--border-light); border-radius: 8px; 
            margin-bottom: 10px; font-size: 14px; font-weight: 600; color: var(--text-secondary); 
            display: flex; align-items: center; gap: 12px; 
        }
        .qc-option.active { border-color: var(--brand-primary); background: rgba(81, 70, 229, 0.08); color: var(--brand-primary); }
        .qc-circle { width: 18px; height: 18px; border-radius: 50%; border: 2px solid var(--border-light); }
        .qc-option.active .qc-circle { border-color: var(--brand-primary); border-width: 5px; }

        .float-elem { 
            position: absolute; z-index: 3; background: var(--surface-white); 
            padding: 16px 20px; border-radius: var(--radius-md); box-shadow: var(--shadow-hover); 
            border: 1px solid var(--border-light); display: flex; align-items: center; gap: 15px; 
        }
        .float-top { top: 20px; right: 20px; animation: float 5s ease-in-out infinite 1s; }
        .float-bottom { bottom: 30px; left: -20px; animation: float 7s ease-in-out infinite 2s; }
        
        .ft-icon { width: 40px; height: 40px; background: rgba(24, 183, 122, 0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .fb-icon { width: 40px; height: 40px; background: rgba(255, 107, 74, 0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        
        .f-title { font-size: 12px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .f-value { font-size: 16px; font-weight: 700; color: var(--text-primary); }

        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-12px); } }

        .section { padding: 100px 5%; text-align: center; }
        .section-header { margin-bottom: 60px; max-width: 600px; margin-left: auto; margin-right: auto; }
        .sec-title { font-size: 36px; font-weight: 800; color: var(--text-primary); margin-bottom: 16px; letter-spacing: -0.5px; }
        .sec-desc { font-size: 16px; color: var(--text-secondary); line-height: 1.6; }

        .category-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 24px; text-align: left; }
        .cat-card { 
            background: var(--surface-white); padding: 30px; border-radius: var(--radius-lg); 
            border: 1px solid var(--border-light); box-shadow: var(--shadow-subtle); 
            text-decoration: none; transition: var(--transition); display: block;
        }
        .cat-card:hover { transform: translateY(-6px); box-shadow: var(--shadow-hover); border-color: var(--brand-primary); }
        .cat-icon { 
            width: 48px; height: 48px; background: rgba(81, 70, 229, 0.1); border-radius: 12px; 
            display: flex; align-items: center; justify-content: center; margin-bottom: 20px; 
            color: var(--brand-primary); font-size: 20px; transition: var(--transition);
        }
        .cat-card:hover .cat-icon { background: var(--brand-primary); color: white; }
        .cat-card h3 { font-size: 18px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px; }
        .cat-card p { font-size: 14px; color: var(--text-secondary); margin-bottom: 20px; }
        .cat-link { font-size: 14px; font-weight: 600; color: var(--brand-primary); display: flex; align-items: center; gap: 5px; }

        .process-wrapper { display: flex; justify-content: space-between; position: relative; margin-top: 40px; text-align: left; }
        .process-line { position: absolute; top: 30px; left: 0; width: 100%; height: 2px; background: var(--border-light); z-index: 1; }
        .step { position: relative; z-index: 2; width: 22%; background: var(--bg-main); }
        .step-num { 
            width: 60px; height: 60px; background: var(--surface-white); border: 2px solid var(--border-light); 
            border-radius: 50%; display: flex; align-items: center; justify-content: center; 
            font-size: 20px; font-weight: 800; color: var(--brand-primary); margin-bottom: 20px; box-shadow: var(--shadow-subtle);
        }
        .step h3 { font-size: 18px; font-weight: 700; color: var(--text-primary); margin-bottom: 10px; }
        .step p { font-size: 14px; color: var(--text-secondary); line-height: 1.5; }

        .diff-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; }
        .diff-card { 
            background: var(--surface-white); padding: 40px 30px; border-radius: var(--radius-lg); 
            border: 1px solid var(--border-light); text-align: center; transition: var(--transition); 
        }
        .diff-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-subtle); }
        .diff-badge { 
            display: inline-block; padding: 6px 12px; border-radius: 20px; 
            font-size: 12px; font-weight: 700; margin-bottom: 20px; letter-spacing: 0.5px;
        }
        .d-easy .diff-badge { background: rgba(108, 99, 255, 0.15); color: var(--brand-secondary); }
        .d-med .diff-badge { background: rgba(81, 70, 229, 0.15); color: var(--brand-primary); }
        .d-hard .diff-badge { background: rgba(255, 107, 74, 0.15); color: var(--accent-coral); }
        .diff-card h3 { font-size: 22px; color: var(--text-primary); margin-bottom: 12px; }
        .diff-card p { font-size: 14px; color: var(--text-secondary); }

        .stats-section { background: var(--surface-white); padding: 80px 5%; border-top: 1px solid var(--border-light); border-bottom: 1px solid var(--border-light); }
        .stats-grid { display: flex; justify-content: space-around; flex-wrap: wrap; gap: 40px; }
        .stat-item { text-align: center; }
        .stat-num { font-size: 56px; font-weight: 800; color: var(--text-primary); letter-spacing: -1px; margin-bottom: 5px; }
        .stat-label { font-size: 14px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px; }

        .cert-section { display: grid; grid-template-columns: 1fr 1.2fr; gap: 60px; padding: 100px 5%; align-items: center; }
        .cert-preview { 
            background: var(--surface-white); padding: 40px; border-radius: 16px; 
            box-shadow: var(--shadow-subtle); border: 1px solid var(--border-light);
            position: relative; overflow: hidden;
        }
        .cert-preview::before { 
            content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 6px; 
            background: linear-gradient(90deg, var(--brand-primary), var(--brand-secondary)); 
        }
        .c-head { text-align: center; margin-bottom: 30px; }
        .c-head h4 { font-size: 24px; font-weight: 800; color: var(--text-primary); letter-spacing: -0.5px; }
        .c-head p { font-size: 12px; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 2px; }
        .c-body { text-align: center; font-size: 14px; color: var(--text-secondary); margin-bottom: 30px; }
        .c-name { font-size: 28px; font-weight: 700; color: var(--brand-primary); margin: 15px 0; border-bottom: 1px solid var(--border-light); display: inline-block; padding-bottom: 5px; }
        .c-foot { display: flex; justify-content: space-between; border-top: 1px solid var(--border-light); padding-top: 20px; }
        .c-stamp { width: 50px; height: 50px; background: rgba(24, 183, 122, 0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--accent-green); }

        .reveal { opacity: 0; transform: translateY(20px); transition: all 0.6s cubic-bezier(0.25, 0.8, 0.25, 1); }
        .reveal.active { opacity: 1; transform: translateY(0); }

        @media (max-width: 992px) {
            .hero { grid-template-columns: 1fr; text-align: center; padding-top: 60px; }
            .hero-content { margin: 0 auto; }
            .hero-actions { justify-content: center; }
            .process-wrapper { flex-direction: column; gap: 30px; }
            .process-line { display: none; }
            .step { width: 100%; text-align: center; display: flex; flex-direction: column; align-items: center; }
            .cert-section { grid-template-columns: 1fr; text-align: center; }
            .diff-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar">
        <a href="index.php" class="logo">
            <div class="logo-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            </div>
            IT Quiz
        </a>
        <div class="nav-links">
            <a href="#categories" class="nav-text">Explore</a>
            <a href="#how-it-works" class="nav-text">How it Works</a>

            <!-- Dark Mode Toggle Button -->
            <button class="theme-toggle" id="themeToggleBtn" aria-label="Toggle dark mode">
                <svg id="sunIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                <svg id="moonIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
            </button>

            <?php if (isset($_SESSION['customer_id'])): ?>
                <a href="customer/dashboard.php" class="btn-primary">Dashboard <span class="arrow">↗</span></a>
                <a href="customer/logout.php" class="btn-logout-nav">Logout</a>
            <?php elseif (isset($_SESSION['supplier_id'])): ?>
                <a href="supplier/dashboard.php" class="btn-primary">Supplier Portal <span class="arrow">↗</span></a>
                <a href="supplier/logout.php" class="btn-logout-nav">Logout</a>
            <?php elseif (isset($_SESSION['admin_id'])): ?>
                <a href="admin/dashboard.php" class="btn-primary">Admin Console <span class="arrow">↗</span></a>
                <a href="admin/logout.php" class="btn-logout-nav">Logout</a>
            <?php else: ?>
                <a href="authentication/supplier_login.php" class="btn-subtle">Supplier</a>
                <a href="authentication/admin_login.php" class="btn-subtle">Admin</a>
                <a href="authentication/customer_login.php" class="nav-text">Log in</a>
                <a href="authentication/customer_register.php" class="btn-primary">Register to Start <span class="arrow">↗</span></a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <header class="hero">
        <div class="hero-content reveal">
            <span class="eyebrow">Online IT Quiz Platform</span>
            <h1 class="hero-title">
                Test Your IT<br>
                Knowledge.<br>
                Prove Your <span class="highlight">Skills.</span>
            </h1>
            <p class="hero-desc">
                Join our premium certification environment. Take dynamically generated evaluations, track your performance, and earn verified credentials.
            </p>
            <div class="hero-actions">
                <?php if (isset($_SESSION['customer_id'])): ?>
                    <a href="#categories" class="btn-primary">Explore Categories <span class="arrow">↓</span></a>
                <?php elseif (isset($_SESSION['supplier_id']) || isset($_SESSION['admin_id'])): ?>
                    <a href="#categories" class="btn-primary">View Categories <span class="arrow">↓</span></a>
                <?php else: ?>
                    <a href="authentication/customer_register.php" class="btn-primary">Register to Start <span class="arrow">↗</span></a>
                <?php endif; ?>
                <a href="#categories" class="nav-text" style="font-weight: 600;">Explore Quizzes</a>
            </div>
        </div>
        
        <div class="hero-visual reveal" style="transition-delay: 0.2s;">
            <svg class="visual-path" viewBox="0 0 500 500" preserveAspectRatio="xMidYMid meet">
                <path id="quiz-path" d="M 400 100 C 500 250, 0 250, 100 400" fill="none" stroke="rgba(81, 70, 229, 0.2)" stroke-width="2" stroke-dasharray="6 6" stroke-linecap="round"/>
                <circle r="4" fill="var(--brand-primary)">
                    <animateMotion dur="6s" repeatCount="indefinite">
                        <mpath href="#quiz-path"/>
                    </animateMotion>
                </circle>
            </svg>

            <div class="float-elem float-top">
                <div class="ft-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-green)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
                <div>
                    <div class="f-title">Time Remaining</div>
                    <div class="f-value">12:45</div>
                </div>
            </div>

            <div class="quiz-card">
                <div class="qc-header">
                    <span>JAVASCRIPT</span>
                    <span>07 / 20</span>
                </div>
                <div class="qc-question">What does a JavaScript function return by default?</div>
                
                <div class="qc-option">
                    <div class="qc-circle"></div> A specific Value
                </div>
                <div class="qc-option active">
                    <div class="qc-circle"></div> Undefined
                </div>
                <div class="qc-option">
                    <div class="qc-circle"></div> An Object
                </div>
            </div>

            <div class="float-elem float-bottom">
                <div class="fb-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-coral)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
                <div>
                    <div class="f-title">Score: 92%</div>
                    <div class="f-value">Certificate Earned</div>
                </div>
            </div>
        </div>
    </header>

    <!-- EXPLORE CATEGORIES -->
    <section class="section" id="categories">
        <div class="section-header reveal">
            <h2 class="sec-title">Explore IT Categories</h2>
            <p class="sec-desc">Choose from our curated library of technical domains. Each category features dynamically generated questions submitted by industry professionals.</p>
        </div>
        
        <div class="category-grid reveal">
            <?php 
            $target_link = isset($_SESSION['customer_id']) ? 'customer/dashboard.php#quizzes' : 'authentication/customer_login.php';
            ?>

            <a href="<?php echo $target_link; ?>" class="cat-card">
                <div class="cat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                </div>
                <h3>Programming</h3>
                <p>Test logic, syntax, and algorithmic problem solving across major languages.</p>
                <div class="cat-link">Start Quiz <span>→</span></div>
            </a>
            
            <a href="<?php echo $target_link; ?>" class="cat-card">
                <div class="cat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                </div>
                <h3>Web Architecture</h3>
                <p>Evaluate front-end and back-end integration, frameworks, and deployment.</p>
                <div class="cat-link">Start Quiz <span>→</span></div>
            </a>
            
            <a href="<?php echo $target_link; ?>" class="cat-card">
                <div class="cat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
                </div>
                <h3>Database Systems</h3>
                <p>Prove your expertise in SQL, NoSQL, data modeling, and query optimization.</p>
                <div class="cat-link">Start Quiz <span>→</span></div>
            </a>

            <a href="<?php echo $target_link; ?>" class="cat-card">
                <div class="cat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                </div>
                <h3>Cyber Security</h3>
                <p>Assess your knowledge on threat mitigation, encryption, and secure coding.</p>
                <div class="cat-link">Start Quiz <span>→</span></div>
            </a>
        </div>
    </section>

    <!-- HOW IT WORKS -->
    <section class="section" style="background: var(--surface-white); border-top: 1px solid var(--border-light);" id="how-it-works">
        <div class="section-header reveal">
            <h2 class="sec-title">The Learning Path</h2>
            <p class="sec-desc">A simple, transparent process to validate your skills and build your professional profile.</p>
        </div>
        
        <div class="process-wrapper reveal">
            <div class="process-line"></div>
            
            <div class="step">
                <div class="step-num">01</div>
                <h3>Choose Topic</h3>
                <p>Select a specific IT category and difficulty tier that matches your current skill level.</p>
            </div>
            
            <div class="step">
                <div class="step-num">02</div>
                <h3>Answer</h3>
                <p>Complete a dynamically generated 10-question evaluation under a strict time limit.</p>
            </div>
            
            <div class="step">
                <div class="step-num">03</div>
                <h3>Score</h3>
                <p>Get instant grading, detailed feedback, and review correct answers immediately.</p>
            </div>
            
            <div class="step">
                <div class="step-num">04</div>
                <h3>Certify</h3>
                <p>Pass the 60% threshold to automatically generate a verifiable digital credential.</p>
            </div>
        </div>
    </section>

    <!-- DIFFICULTY LEVELS -->
    <section class="section">
        <div class="section-header reveal">
            <h2 class="sec-title">Structured Difficulty</h2>
            <p class="sec-desc">Evaluations are categorized into three distinct tiers, allowing you to build confidence or prove mastery.</p>
        </div>
        
        <div class="diff-grid reveal">
            <div class="diff-card d-easy">
                <div class="diff-badge">EASY</div>
                <h3>Foundations</h3>
                <p>Start building confidence. Focuses on core definitions, basic syntax, and introductory concepts.</p>
            </div>
            <div class="diff-card d-med">
                <div class="diff-badge">MEDIUM</div>
                <h3>Application</h3>
                <p>Push your understanding. Requires contextual knowledge, debugging, and mid-level logic.</p>
            </div>
            <div class="diff-card d-hard">
                <div class="diff-badge">HARD</div>
                <h3>Expertise</h3>
                <p>Prove your expertise. Features complex edge cases, advanced algorithms, and system analysis.</p>
            </div>
        </div>
    </section>

    <!-- STATISTICS -->
    <section class="stats-section reveal">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-num counter" data-target="<?php echo $total_questions; ?>">0</div>
                <div class="stat-label">Live Questions</div>
            </div>
            <div class="stat-item">
                <div class="stat-num counter" data-target="<?php echo $total_attempts; ?>">0</div>
                <div class="stat-label">Quizzes Taken</div>
            </div>
            <div class="stat-item">
                <div class="stat-num">3</div>
                <div class="stat-label">Difficulty Tiers</div>
            </div>
            <div class="stat-item">
                <div class="stat-num counter" data-target="<?php echo $total_certificates; ?>">0</div>
                <div class="stat-label">Certificates Earned</div>
            </div>
        </div>
    </section>

    <!-- CERTIFICATION PREVIEW -->
    <section class="cert-section reveal">
        <div>
            <h2 class="sec-title">Earn Verified Credentials</h2>
            <p class="sec-desc" style="margin-bottom: 30px;">
                Every time you successfully pass an evaluation, the system automatically generates a unique Certificate of Completion. Keep a record of your progress, download your PDFs, and share your validated skills with employers.
            </p>
            <?php if (!isset($_SESSION['customer_id'])): ?>
                <a href="authentication/customer_register.php" class="btn-primary">Start Earning Today <span class="arrow">↗</span></a>
            <?php endif; ?>
        </div>
        
        <div class="cert-preview">
            <div class="c-head">
                <h4>Certificate of Completion</h4>
                <p>Verified Professional Credential</p>
            </div>
            <div class="c-body">
                This is proudly presented to
                <div class="c-name">Student Name</div>
                <br>
                for successfully passing the official certification evaluation with a score of <strong>92%</strong>.
            </div>
            <div class="c-foot">
                <div style="font-size: 11px; color: var(--text-secondary); font-weight: 600;">
                    DATE ISSUED<br>
                    <span style="color: var(--text-primary); font-size: 13px;">Oct 24, 2024</span>
                </div>
                <div class="c-stamp">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
            </div>
        </div>
    </section>

    <script>
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

        function reveal() {
            var reveals = document.querySelectorAll(".reveal");
            for (var i = 0; i < reveals.length; i++) {
                var windowHeight = window.innerHeight;
                var elementTop = reveals[i].getBoundingClientRect().top;
                if (elementTop < windowHeight - 80) reveals[i].classList.add("active");
            }
        }
        window.addEventListener("scroll", reveal);
        reveal(); 

        const counters = document.querySelectorAll('.counter');
        counters.forEach(counter => {
            const updateCount = () => {
                const target = +counter.getAttribute('data-target');
                const count = +counter.innerText;
                const inc = Math.max(1, Math.ceil(target / 40)); 

                if (count < target) {
                    counter.innerText = count + inc > target ? target : count + inc;
                    setTimeout(updateCount, 40);
                } else {
                    counter.innerText = target;
                }
            };
            
            let observer = new IntersectionObserver(function(entries) {
                if(entries[0].isIntersecting === true) {
                    updateCount();
                    observer.unobserve(counter);
                }
            }, { threshold: [0] });
            
            observer.observe(counter);
        });
    </script>
</body>
</html>