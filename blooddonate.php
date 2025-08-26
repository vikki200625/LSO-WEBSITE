<?php
require_once __DIR__ . '/require_login.php';
require_once __DIR__ . '/config.php';

$conn = db_connect('donationdb');

$result = $conn->query("SELECT id, title, content, image_url, author, created_at FROM articles ORDER BY created_at DESC");
$articles = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$conn->close();

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf'];
$minDate = date('Y-m-d');
$status = $_GET['status'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Blood Donation Camp - MyWebsite</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
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
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* Header Styles */
    header {
        text-align: center;
        padding: 40px 0 20px;
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
        margin: 0 0 10px;
        color: var(--text);
    }

    header p {
        color: var(--muted);
        font-size: 1.1rem;
        max-width: 600px;
        margin: 0 auto;
    }

    /* Alert Styles */
    .alert {
        max-width: 600px;
        margin: 0 auto 25px;
        padding: 15px 20px;
        border-radius: var(--radius);
        text-align: center;
        font-weight: 500;
    }

    .success {
        background: #e7f7ec;
        color: #1c7c3a;
        border: 1px solid #b9e2c7;
    }

    .error {
        background: #fdecea;
        color: #9f3a38;
        border: 1px solid #f5c6cb;
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
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .content-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    }

    .content-box img {
        width: 100%;
        height: 220px;
        object-fit: cover;
        border-radius: var(--radius);
        margin-bottom: 15px;
        border: 1px solid #f0f0f0;
    }

    .content-box h2 {
        font-size: 1.4rem;
        margin: 0 0 15px;
        color: var(--accent);
    }

    .meta-info {
        color: var(--muted);
        font-size: 0.9rem;
        margin: 0 0 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f0f0f0;
    }

    .content-box p {
        color: var(--muted);
        line-height: 1.6;
        margin: 0;
    }

    /* Form Card */
    .form-card {
        background: var(--white);
        border: 1px solid #eee;
        border-radius: var(--radius);
        padding: 30px;
        box-shadow: var(--shadow);
        max-width: 500px;
        margin: 0 auto 50px;
    }

    .form-card h3 {
        font-size: 1.6rem;
        margin: 0 0 15px;
        color: var(--text);
        text-align: center;
    }

    .form-card input,
    .form-card textarea,
    .form-card button {
        width: 100%;
        padding: 15px;
        margin: 12px 0;
        border: 1px solid #eee;
        border-radius: var(--radius);
        font-size: 1rem;
        background: var(--cream);
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
        box-sizing: border-box;
    }

    .form-card input:focus,
    .form-card textarea:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(212, 163, 115, 0.1);
    }

    .form-card textarea {
        resize: vertical;
        min-height: 100px;
        font-family: inherit;
    }

    .form-card button {
        background: var(--accent);
        color: var(--white);
        border: none;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s ease, transform 0.2s ease;
    }

    .form-card button:hover {
        background: var(--accent-dark);
        transform: translateY(-2px);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--muted);
        grid-column: 1 / -1;
    }

    .empty-state h3 {
        font-size: 1.5rem;
        margin: 0 0 15px;
        color: var(--text);
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .content-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .content-grid {
            grid-template-columns: 1fr;
            padding: 0 15px;
        }

        header h1 {
            font-size: 2.2rem;
        }

        .form-card {
            margin: 0 15px 40px;
            padding: 25px;
        }
    }

    @media (max-width: 480px) {
        .content-box {
            padding: 20px;
        }

        .form-card {
            padding: 20px;
        }
    }
  </style>
</head>
<body>
  <header>
    <div class="container">
        <a href="index.php">← Back to Home</a>
        <h1>🩸 Blood Donation Camp</h1>
        <p>Read inspiring stories and book your appointment to save lives. Every drop counts!</p>
    </div>
  </header>

  <div class="container">
    <?php if ($status === 'booked'): ?>
        <div class="alert success">✅ Your appointment request was submitted successfully! We will contact you for confirmation.</div>
    <?php elseif ($status === 'error'): ?>
        <div class="alert error">❌ There was a problem submitting your appointment. Please try again.</div>
    <?php endif; ?>

    <main class="content-grid">
        <?php if (empty($articles)): ?>
            <div class="empty-state">
                <h3>No articles yet 📝</h3>
                <p>Check back soon for inspiring stories about our blood donation drives and the lives we've saved together!</p>
            </div>
        <?php else: ?>
            <?php foreach ($articles as $a): ?>
                <article class="content-box">
                    <?php if (!empty($a['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($a['image_url']); ?>" alt="<?php echo htmlspecialchars($a['title']); ?>">
                    <?php endif; ?>
                    <h2>❤️ <?php echo htmlspecialchars($a['title']); ?></h2>
                    <p class="meta-info">
                        By <strong><?php echo htmlspecialchars($a['author']); ?></strong> on <?php echo date("F j, Y", strtotime($a['created_at'])); ?>
                    </p>
                    <p><?php echo nl2br(htmlspecialchars($a['content'])); ?></p>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <section class="form-card">
        <h3>📅 Book Your Life-Saving Appointment</h3>
        <p style="color: var(--muted); text-align: center; margin: 0 0 20px;">Join us in making a difference - your donation can save up to 3 lives!</p>
        
        <form action="book_appointment.php" method="post">
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
            <input type="text" name="name" placeholder="👤 Full Name" required>
            <input type="tel" name="phone" placeholder="📞 Phone Number" pattern="[0-9+\-\s()]{7,20}" required>
            <textarea name="address" placeholder="🏠 Complete Address" rows="3" required></textarea>
            <input type="date" name="appointment_date" min="<?php echo $minDate; ?>" required>
            <button type="submit">Book My Appointment 🩸</button>
        </form>
    </section>
  </div>
</body>
</html>