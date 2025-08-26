<?php
require_once __DIR__ . '/require_login.php';
require_once __DIR__ . '/config.php';
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'donationdb';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) { die('DB connection failed'); }

$errors = [];
$success = false;
$donationData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name']    ?? '');
    $address = trim($_POST['address'] ?? '');
    $aadhar  = trim($_POST['aadhar']  ?? '');
    $pancard = trim($_POST['pancard'] ?? '');
    $amount  = floatval($_POST['amount'] ?? 0);

    if ($name === '' || $address === '' || $aadhar === '' || $pancard === '') {
        $errors[] = 'All fields are required';
    }
    if ($amount < 250) {
        $errors[] = 'Minimum donation is 250 Rs';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare(
            'INSERT INTO donations (name, address, aadhar, pancard, amount) VALUES (?,?,?,?,?)'
        );
        $stmt->bind_param('ssssd', $name, $address, $aadhar, $pancard, $amount);
        if ($stmt->execute()) {
            $success = true;
            $donationData = [
                'name' => $name,
                'amount' => $amount
            ];
        } else {
            $errors[] = 'Could not save donation';
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Donation Result - MyWebsite</title>
    <style>
        :root {
            --cream: #fdfaf5;
            --white: #ffffff;
            --text: #333333;
            --muted: #6b7280;
            --accent: #d4a373;
            --accent-dark: #b78147;
            --shadow: 0 8px 20px rgba(0,0,0,0.05);
            --radius: 12px;
            --gradient: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            --success: #10b981;
            --error: #ef4444;
        }

        * { box-sizing: border-box; }
        
        body {
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(180deg, var(--white) 0%, var(--cream) 100%);
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        header {
            text-align: center;
            padding: 40px 0;
            background: linear-gradient(180deg, var(--white), var(--cream));
            border-bottom: 1px solid #eee;
        }

        header a {
            display: inline-block;
            margin-bottom: 20px;
            color: var(--accent);
            font-weight: bold;
            text-decoration: none;
            transition: color 0.2s;
            padding: 10px 20px;
            border: 2px solid var(--accent);
            border-radius: var(--radius);
        }

        header a:hover {
            background: var(--accent);
            color: var(--white);
        }

        header h1 {
            font-size: 2.8rem;
            margin: 0;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 800;
        }

        /* Result Cards */
        .result-card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 40px;
            margin: 60px auto;
            box-shadow: var(--shadow);
            text-align: center;
            position: relative;
            overflow: hidden;
            max-width: 600px;
        }

        /* Success Card */
        .success-card {
            border-top: 5px solid var(--success);
        }

        .success-icon {
            font-size: 5rem;
            margin-bottom: 20px;
            animation: successPulse 1s ease-in-out infinite alternate;
        }

        @keyframes successPulse {
            from { transform: scale(1); }
            to { transform: scale(1.1); }
        }

        .success-title {
            font-size: 2rem;
            color: var(--success);
            margin: 0 0 20px;
            font-weight: 700;
        }

        .donation-amount {
            font-size: 3rem;
            font-weight: 800;
            color: var(--accent);
            margin: 20px 0;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .donor-name {
            font-size: 1.5rem;
            color: var(--text);
            margin: 10px 0 30px;
        }

        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            background: var(--accent);
            animation: confettiFall 3s linear infinite;
        }

        @keyframes confettiFall {
            0% {
                transform: translateY(-100vh) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(100vh) rotate(720deg);
                opacity: 0;
            }
        }

        /* Error Card */
        .error-card {
            border-top: 5px solid var(--error);
        }

        .error-icon {
            font-size: 5rem;
            margin-bottom: 20px;
            color: var(--error);
            animation: errorShake 0.5s ease-in-out infinite alternate;
        }

        @keyframes errorShake {
            from { transform: translateX(-5px); }
            to { transform: translateX(5px); }
        }

        .error-title {
            font-size: 2rem;
            color: var(--error);
            margin: 0 0 20px;
            font-weight: 700;
        }

        .error-list {
            list-style: none;
            padding: 0;
            margin: 20px 0 30px;
            text-align: left;
        }

        .error-list li {
            background: #fef2f2;
            color: var(--error);
            padding: 15px 20px;
            border-radius: var(--radius);
            margin-bottom: 10px;
            border-left: 4px solid var(--error);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .error-list li::before {
            content: "⚠️";
            font-size: 1.2rem;
        }

        /* Buttons */
        .btn {
            display: inline-block;
            padding: 15px 30px;
            border-radius: var(--radius);
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s ease;
            margin: 10px;
            font-size: 1.1rem;
        }

        .btn-primary {
            background: var(--gradient);
            color: var(--white);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(212, 163, 115, 0.3);
        }

        .btn-secondary {
            background: transparent;
            color: var(--accent);
            border: 2px solid var(--accent);
        }

        .btn-secondary:hover {
            background: var(--accent);
            color: var(--white);
        }

        /* Decorative Elements */
        .floating-hearts {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .heart {
            position: absolute;
            font-size: 2rem;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(10deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            header h1 {
                font-size: 2.2rem;
            }
            
            .result-card {
                margin: 40px 20px;
                padding: 30px 20px;
            }
            
            .donation-amount {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Floating decorative hearts -->
    <div class="floating-hearts">
        <div class="heart" style="top: 10%; left: 5%; animation-delay: 0s;">❤️</div>
        <div class="heart" style="top: 30%; left: 90%; animation-delay: 1s;">💖</div>
        <div class="heart" style="top: 70%; left: 10%; animation-delay: 2s;">💝</div>
        <div class="heart" style="top: 90%; left: 80%; animation-delay: 3s;">💕</div>
    </div>

    <header>
        <div class="container">
            <a href="index.php">← Back to Home</a>
            <h1>Donation Result</h1>
        </div>
    </header>

    <div class="container">
        <?php if ($success): ?>
            <!-- Success Card -->
            <div class="result-card success-card">
                <!-- Confetti animation -->
                <div class="confetti" style="left: 10%; animation-delay: 0s;"></div>
                <div class="confetti" style="left: 20%; animation-delay: 0.2s; background: var(--success);"></div>
                <div class="confetti" style="left: 30%; animation-delay: 0.4s;"></div>
                <div class="confetti" style="left: 40%; animation-delay: 0.6s; background: var(--success);"></div>
                <div class="confetti" style="left: 50%; animation-delay: 0.8s;"></div>
                <div class="confetti" style="left: 60%; animation-delay: 1s; background: var(--success);"></div>
                <div class="confetti" style="left: 70%; animation-delay: 1.2s;"></div>
                <div class="confetti" style="left: 80%; animation-delay: 1.4s; background: var(--success);"></div>
                <div class="confetti" style="left: 90%; animation-delay: 1.6s;"></div>
                
                <div class="success-icon">🎉</div>
                <h2 class="success-title">Thank You for Your Generosity!</h2>
                <p class="donor-name">Dear <?= htmlspecialchars($donationData['name']) ?>,</p>
                <p>Your donation has been successfully processed and will make a real difference in someone's life.</p>
                <div class="donation-amount">₹<?= number_format($donationData['amount'], 2) ?></div>
                <p>Your contribution will help us continue our mission of creating positive change in communities across the country.</p>
                
                <div style="margin-top: 30px;">
                    <a href="donate.php" class="btn btn-primary">Make Another Donation</a>
                    <a href="index.php" class="btn btn-secondary">Return to Home</a>
                </div>
            </div>
        <?php else: ?>
            <!-- Error Card -->
            <div class="result-card error-card">
                <div class="error-icon">😔</div>
                <h2 class="error-title">Donation Processing Failed</h2>
                <p>We encountered some issues while processing your donation. Please review the information below and try again.</p>
                
                <?php if ($errors): ?>
                    <ul class="error-list">
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                
                <p>If you continue to experience issues, please contact our support team for assistance.</p>
                
                <div style="margin-top: 30px;">
                    <a href="donate.php" class="btn btn-primary">Try Again</a>
                    <a href="index.php" class="btn btn-secondary">Return to Home</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Create more confetti elements dynamically
        document.addEventListener('DOMContentLoaded', function() {
            if (document.querySelector('.success-card')) {
                const confettiContainer = document.querySelector('.success-card');
                const colors = ['#d4a373', '#b78147', '#10b981', '#ffffff'];
                
                for (let i = 0; i < 30; i++) {
                    const confetti = document.createElement('div');
                    confetti.className = 'confetti';
                    confetti.style.left = Math.random() * 100 + '%';
                    confetti.style.animationDelay = Math.random() * 2 + 's';
                    confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
                    confetti.style.width = Math.random() * 10 + 5 + 'px';
                    confetti.style.height = confetti.style.width;
                    confetti.style.borderRadius = Math.random() > 0.5 ? '50%' : '0';
                    confettiContainer.appendChild(confetti);
                }
            }
        });
    </script>
</body>
</html>