<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ScholarFlow - College Scholarship Application System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Additional enhancements compatible with existing CSS */
        .hero {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;

    background-image: url('uploads/3.png');
    background-size: cover;     /* SHOW FULL IMAGE */
    background-position: center;
    background-repeat: no-repeat;

    background-color: black; /* fills empty space */

    position: relative;
}

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: float 20s linear infinite;
            opacity: 0.3;
        }

        @keyframes float {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 20px;
            animation: fadeInUp 0.8s ease;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            max-width: 800px;
            line-height: 1.2;
        }

        .hero p {
            font-size: 1.25rem;
            max-width: 600px;
            margin-bottom: 40px;
            animation: fadeInUp 0.8s ease 0.2s both;
            opacity: 0.9;
            line-height: 1.6;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .btn-group {
            display: flex;
            gap: 20px;
            animation: fadeInUp 0.8s ease 0.4s both;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 200px;
        }

        .btn {
            padding: 15px 40px;
            font-size: 1.1rem;
            border-radius: 50px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            border: 2px solid transparent;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255,255,255,0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn:hover {
            background: white;
    color: #771111;
        }

        .btn-secondary {
        background: transparent;
        border: 2px solid white;
        color: white;
        transition: all 0.3s ease;
        }

       .btn-secondary:hover {
        background: white;
        color: #771111;  /* text turns red */
        }

        /* Features section */
        .features {
            padding: 80px 20px;
            background: #771111;
            text-align: center;
        }

        .features h2 {
            color: #ffffff;
            font-size: 2.5rem;
            margin-bottom: 50px;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: white;
            padding: 40px 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 20px;
        }

        .feature-card h3 {
            color: #771111;
            margin-bottom: 15px;
            font-size: 1.5rem;
        }

        .feature-card p {
            color: #666;
            line-height: 1.6;
        }

        /* Stats section */
        .stats {
            background: #1976d2;
            color: white;
            padding: 60px 20px;
            display: flex;
            justify-content: center;
            gap: 60px;
            flex-wrap: wrap;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            display: block;
        }

        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* Footer */
        footer {
            background: #cea10e;
            color: white;
            text-align: center;
            padding: 30px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
                padding: 0 20px;
            }
            
            .hero p {
                font-size: 1rem;
                padding: 0 20px;
            }
            
            .btn {
                padding: 12px 30px;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>

    <!-- Hero Section -->
    <div class="hero">

        <div class="btn-group">
            <a href="login.php" class="btn btn-login">Login</a>
    <a href="register.php" class="btn btn-secondary">Create Account</a>
        </div>
    </div>

    <!-- Features Section -->
    <section class="features">
        <h2>Why Choose ScholarFlow?</h2>
        <div class="feature-grid">
            <div class="feature-card">
                <div class="feature-icon">📝</div>
                <h3>Easy Application</h3>
                <p>Simple, step-by-step forms guide you through the entire scholarship application process.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📄</div>
                <h3>Document Upload</h3>
                <p>Securely upload transcripts, essays, and recommendation letters in one place.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3>Track Status</h3>
                <p>Real-time updates on your application status from submission to decision.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <h3>Secure & Private</h3>
                <p>Your data is encrypted and protected with industry-standard security.</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; 2026 ScholarFlow. Empowering education through technology.</p>
    </footer>

</body>
</html>