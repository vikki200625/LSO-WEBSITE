<?php
// Make sure user is logged in to see this page
require_once __DIR__ . '/require_login.php';
require_once __DIR__ . '/config.php';

// --- CHANGE WAS HERE ---
// Corrected the database connection to point to 'donationdb' where the content lives.
$conn = db_connect('userdb');   // <-- fixed DB name

// Fetch content from the donation_content table
$content_items = [];
$result = $conn->query("SELECT title, content, image_path FROM donation_content ORDER BY created_at DESC");
if ($result) {
    $content_items = $result->fetch_all(MYSQLI_ASSOC);
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Make a Donation - MyWebsite</title>
    
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
        }
        
        * { box-sizing: border-box; }
        
        body {
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--white);
            color: var(--text);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header Styles */
        header {
            text-align: center;
            margin-bottom: 40px;
            padding: 50px 0;
            background: linear-gradient(135deg, var(--cream) 0%, var(--white) 100%);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }
        
        header::before {
            content: '❤️';
            font-size: 4rem;
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0.1;
        }
        
        header h1 {
            font-size: 3rem;
            margin: 0 0 15px;
            color: var(--text);
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 800;
        }
        
        header p {
            color: var(--muted);
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto 25px;
            line-height: 1.6;
        }
        
        header a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            color: var(--accent);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            padding: 10px 20px;
            border: 2px solid var(--accent);
            border-radius: var(--radius);
            background: transparent;
        }
        
        header a:hover {
            background: var(--accent);
            color: var(--white);
            transform: translateY(-2px);
        }
        
        /* Inspiration Section */
        .inspiration-section {
            text-align: center;
            padding: 50px 20px;
            background: var(--cream);
            border-radius: var(--radius);
            margin: 40px auto;
            max-width: 800px;
            box-shadow: var(--shadow);
        }
        
        .inspiration-section h2 {
            font-size: 2rem;
            margin: 0 0 20px;
            color: var(--text);
        }
        
        .inspiration-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        
        .inspiration-card {
            background: var(--white);
            padding: 25px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .inspiration-card:hover {
            transform: translateY(-5px);
        }
        
        .inspiration-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .inspiration-card h3 {
            font-size: 1.2rem;
            margin: 0 0 15px;
            color: var(--accent);
        }
        
        .inspiration-card p {
            color: var(--muted);
            line-height: 1.6;
            margin: 0;
            font-size: 0.95rem;
        }
        
        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
            padding: 0 20px;
        }
        
        .content-box {
            background: var(--white);
            border: 1px solid #eee;
            border-radius: var(--radius);
            padding: 25px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .content-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: var(--gradient);
        }
        
        .content-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        
        .content-box h2 {
            font-size: 1.4rem;
            margin: 0 0 15px;
            color: var(--accent-dark);
            font-weight: 700;
        }
        
        .content-box img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: var(--radius);
            margin-bottom: 15px;
            border: 1px solid #f0f0f0;
            transition: transform 0.3s ease;
        }
        
        .content-box:hover img {
            transform: scale(1.05);
        }
        
        .content-box p {
            color: var(--muted);
            line-height: 1.6;
            margin: 0;
            font-size: 1rem;
        }
        
        /* Donation Form Section */
        .donation-form-section {
            background: var(--white);
            border: 1px solid #eee;
            border-radius: var(--radius);
            padding: 35px;
            box-shadow: var(--shadow);
            max-width: 500px;
            margin: 0 auto 50px;
            position: relative;
            overflow: hidden;
        }
        
        .donation-form-section::before {
            content: '🎁';
            position: absolute;
            top: -20px;
            right: -20px;
            font-size: 8rem;
            opacity: 0.05;
            z-index: 0;
        }
        
        .donation-form-section h3 {
            font-size: 1.6rem;
            margin: 0 0 15px;
            color: var(--text);
            text-align: center;
            position: relative;
            z-index: 1;
        }
        
        .donation-form-section p {
            color: var(--muted);
            text-align: center;
            margin: 0 0 25px;
            font-size: 1rem;
            line-height: 1.6;
            position: relative;
            z-index: 1;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }
        
        .donation-form-section input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #eee;
            border-radius: var(--radius);
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--cream);
            font-family: inherit;
        }
        
        .donation-form-section input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(212, 163, 115, 0.15);
            transform: translateY(-2px);
        }
        
        .donation-form-section input::placeholder {
            color: #999;
        }
        
        .donation-form-section button {
            width: 100%;
            padding: 16px;
            background: var(--gradient);
            color: var(--white);
            border: none;
            border-radius: var(--radius);
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .donation-form-section button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .donation-form-section button:hover::before {
            left: 100%;
        }
        
        .donation-form-section button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(212, 163, 115, 0.3);
        }
        
        /* Impact Stats */
        .impact-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 40px 0;
            padding: 0 20px;
        }
        
        .stat-card {
            background: var(--white);
            padding: 25px;
            border-radius: var(--radius);
            text-align: center;
            box-shadow: var(--shadow);
            border: 1px solid #eee;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--accent);
            margin: 0 0 10px;
        }
        
        .stat-label {
            color: var(--muted);
            font-size: 1rem;
            margin: 0;
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .inspiration-content {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
                padding: 0 15px;
            }
            
            header h1 {
                font-size: 2.5rem;
            }
            
            .donation-form-section {
                margin: 0 15px 40px;
                padding: 30px;
            }
            
            .impact-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 480px) {
            .content-grid {
                gap: 25px;
            }
            
            .content-box {
                padding: 20px;
            }
            
            .donation-form-section {
                padding: 25px;
            }
            
            .impact-stats {
                grid-template-columns: 1fr;
            }
            
            header h1 {
                font-size: 2rem;
            }
        }
        
        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: var(--muted);
            grid-column: 1 / -1;
        }
        
        .empty-state h3 {
            font-size: 1.8rem;
            margin: 0 0 15px;
            color: var(--text);
        }
        
        .empty-state p {
            font-size: 1.1rem;
            max-width: 500px;
            margin: 0 auto;
        }
        
        /* Floating Elements */
        .floating-heart {
            position: fixed;
            font-size: 2rem;
            opacity: 0.1;
            z-index: -1;
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(10deg); }
        }
    </style>
</head>
<body>
    <!-- Floating decorative elements -->
    <div class="floating-heart" style="top: 20%; left: 5%;">❤️</div>
    <div class="floating-heart" style="top: 60%; right: 5%;">✨</div>
    <div class="floating-heart" style="top: 80%; left: 10%;">🌟</div>

    <header>
        <div class="container">
            <a href="index.php">← Back to Home</a>
            <h1>DONATE WITH PURPOSE 🌟</h1>
            <p>Your generosity creates ripples of change that transform lives and build stronger communities. Every contribution matters.</p>
        </div>
    </header>

    <div class="container">
        <!-- Inspiration Section -->
        <section class="inspiration-section">
            <h2>Why Your Donation Matters 🌈</h2>
            <p>Your support doesn't just provide resources—it creates opportunities, fosters hope, and builds a better tomorrow for those in need.</p>
            
            <div class="inspiration-content">
                <div class="inspiration-card">
                    <div class="inspiration-icon">❤️</div>
                    <h3>Immediate Impact</h3>
                    <p>Your donation provides immediate relief and support to those who need it most, creating tangible change right now.</p>
                </div>
                
                <div class="inspiration-card">
                    <div class="inspiration-icon">🌱</div>
                    <h3>Sustainable Growth</h3>
                    <p>We focus on long-term solutions that create sustainable change and empower communities to thrive independently.</p>
                </div>
                
                <div class="inspiration-card">
                    <div class="inspiration-icon">🤝</div>
                    <h3>Community Building</h3>
                    <p>Your contribution helps build stronger, more resilient communities where everyone has the opportunity to succeed.</p>
                </div>
            </div>
        </section>

        <!-- Impact Statistics -->
        <div class="impact-stats">
            <div class="stat-card">
                <div class="stat-number">2,500+</div>
                <div class="stat-label">Lives Impacted 🌟</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">₹1.2M+</div>
                <div class="stat-label">Total Donations 💰</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">15+</div>
                <div class="stat-label">Communities Served 🏘️</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">98%</div>
                <div class="stat-label">Direct to Cause ✅</div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <?php if (empty($content_items)): ?>
                <div class="empty-state">
                    <h3>No donation stories yet 📝</h3>
                    <p>Be the first to make an impact! Your story could inspire others to join this beautiful journey of giving.</p>
                </div>
            <?php else: ?>
                <?php foreach ($content_items as $item): ?>
                    <article class="content-box">
                        <h2>✨ <?php echo htmlspecialchars($item['title']); ?></h2>
                        <?php if (!empty($item['image_path'])): ?>
                            <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                        <?php endif; ?>
                        <p><?php echo nl2br(htmlspecialchars($item['content'])); ?></p>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Donation Form Section -->
        <section class="donation-form-section">
            <h3>Make Your Impact Today 🎯</h3>
            <p>Join thousands of generous donors who are making a real difference. Every contribution, no matter the size, creates lasting change. 🌟</p>

            <form action="process_donation.php" method="post">
                <div class="form-group">
                    <input type="text" name="name" placeholder="👤 Full Name" required>
                </div>
                <div class="form-group">
                    <input type="text" name="address" placeholder="🏠 Complete Address" required>
                </div>
                <div class="form-group">
                    <input type="text" name="aadhar" placeholder="🆔 Aadhar Card Number" required>
                </div>
                <div class="form-group">
                    <input type="text" name="pancard" placeholder="💳 PAN Card Number" required>
                </div>
                <div class="form-group">
                    <input type="number" name="amount" placeholder="💰 Amount (Minimum ₹250)" min="250" step="1" required>
                </div>
                <button type="submit">Donate Now 💫</button>
            </form>
        </section>
    </div>
</body>
</html>