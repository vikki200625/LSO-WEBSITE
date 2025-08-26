<?php
require_once __DIR__ . '/config.php';

$loggedIn = !empty($_SESSION['user_id']);
$isAdmin  = !empty($_SESSION['admin_logged_in']);

/* -------------------------------------------------
   Get the logged-in user's name (if any)
   ------------------------------------------------- */
$userName = '';
if ($loggedIn) {
    // Use the same DB helper you already have
    $conn = db_connect('userdb');               // <-- adjust DB name if needed
    $stmt = $conn->prepare('SELECT name FROM users WHERE id = ?');
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows) {
        $row = $result->fetch_assoc();
        $userName = htmlspecialchars($row['name']);
    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Welcome to MyWebsite - Live Better, Give Better</title>
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
        --blue: #3b82f6;
        --blue-dark: #2563eb;
    }
    * { box-sizing: border-box; }
    html, body { margin:0; padding:0; font-family:system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; color:var(--text); background-color:var(--white); }
    a { color:inherit; text-decoration:none; }
    img { max-width:100%; display:block; }
    .container { max-width:1100px; margin:0 auto; padding:0 20px; }

    /* ---------- Header & Nav ---------- */
    header {
        background:rgba(255,255,255,0.8);
        border-bottom:1px solid #eee;
        position:sticky; top:0; z-index:1000;
        backdrop-filter:saturate(1.5) blur(8px);
        padding:12px 20px;
    }
    .nav-bar {
        display:flex; 
        align-items:center; 
        justify-content:space-between; 
        gap:16px; 
        position:relative;
    }
    .nav-bar a { font-weight:bold; }
    .nav-bar button { font-size:20px; background:none; border:none; cursor:pointer; }

    /* ---------- Profile Icon & Dropdown ---------- */
    .profile-wrapper {
        position:relative;
        margin-left:20px;
    }
    .user-profile-icon {
        width:40px; height:40px; border-radius:50%; background:var(--accent);
        display:flex; align-items:center; justify-content:center;
        cursor:pointer; transition:transform .3s ease, box-shadow .3s ease;
        border:2px solid var(--white); box-shadow:0 2px 8px rgba(0,0,0,.1);
    }
    .user-profile-icon:hover {
        transform:scale(1.05);
        box-shadow:0 4px 12px rgba(0,0,0,.15);
    }
    .user-profile-icon svg { width:24px; height:24px; fill:var(--white); }

    /* Logged-in user menu */
    .menu-container-user {
        display:none;
        position:absolute; top:50px; right:0;
        background:#fff; border:1px solid #ddd; border-radius:8px;
        box-shadow:0 8px 24px rgba(0,0,0,.12);
        min-width:220px; z-index:2001;
        opacity:0; transform:translateY(-10px);
        transition:opacity .4s ease, transform .4s ease;
    }
    .menu-container-user.show {
        display:block; opacity:1; transform:translateY(0);
    }
    /* Greeting line styling in dropdown */
    .menu-container-user li:first-child {
        text-align: center;
        font-weight: bold;
        background-color: #f5f5f5; /* light gray background */
        color: #333;               /* darker text */
        padding: 12px 20px;
        border-bottom: 1px solid #ddd; /* subtle separator */
        cursor: default; /* makes it look non-clickable */
    }

    .menu-container-user ul { list-style:none; padding:8px 0; margin:0; }
    .menu-container-user li {
        opacity:0;
        animation:fadeInItem .3s ease forwards;
    }
    .menu-container-user li:nth-child(1) { animation-delay:.05s; }
    .menu-container-user li:nth-child(2) { animation-delay:.10s; }
    .menu-container-user li:nth-child(3) { animation-delay:.15s; }
    .menu-container-user li:nth-child(4) { animation-delay:.20s; }
    .menu-container-user li:nth-child(5) { animation-delay:.25s; }
    .menu-container-user li:nth-child(6) { animation-delay:.30s; }
    @keyframes fadeInItem {
        from { opacity:0; transform:translateX(10px); }
        to   { opacity:1; transform:translateX(0); }
    }
    .menu-container-user li a {
        display:block; padding:12px 20px; color:#333; font-size:.95rem;
        transition:background .2s ease, padding-left .2s ease;
    }
    .menu-container-user li a:hover {
        background:#f8f8f8; padding-left:25px;
    }
    .menu-container-user li:not(:last-child) { border-bottom:1px solid #f0f0f0; }

    /* Hamburger menu (non-logged-in) */
    .menu-container {
        display:none;
        position:fixed;
        top:60px;
        right:20px;
        background:#fff;
        border:1px solid #ddd;
        border-radius:8px;
        box-shadow:0 8px 24px rgba(0,0,0,.12);
        min-width:220px;
        z-index:2001;
        opacity:0;
        transform:translateY(-10px);
        transition:opacity .4s ease, transform .4s ease;
    }
    .menu-container.show {
        display:block;
        opacity:1;
        transform:translateY(0);
    }
    .menu-container ul { list-style:none; padding:8px 0; margin:0; }
    .menu-container li {
        opacity:0;
        animation:fadeInItem .3s ease forwards;
    }
    .menu-container li:nth-child(1) { animation-delay:.05s; }
    .menu-container li:nth-child(2) { animation-delay:.10s; }
    .menu-container li:nth-child(3) { animation-delay:.15s; }
    .menu-container li:nth-child(4) { animation-delay:.20s; }
    .menu-container li:nth-child(5) { animation-delay:.25s; }
    .menu-container li:nth-child(6) { animation-delay:.30s; }
    .menu-container li a {
        display:block; padding:12px 20px; color:#333; font-size:.95rem;
        transition:background .2s ease, padding-left .2s ease;
    }
    .menu-container li a:hover {
        background:#f8f8f8; padding-left:25px;
    }
    .menu-container li:not(:last-child) { border-bottom:1px solid #f0f0f0; }

    /* ---------- Hero ---------- */
    .hero { text-align:center; padding:60px 20px; background-color:var(--cream); }
    .hero h1 { font-size:2.5rem; margin:0 0 10px; }
    .hero p { font-size:1.1rem; color:var(--muted); max-width:700px; margin:0 auto 20px; }
    .btn { display:inline-block; padding:12px 24px; border-radius:var(--radius); font-weight:bold; transition:transform .2s; }
    .btn-primary { background-color:var(--accent); color:var(--white); }
    .btn-secondary { background-color:var(--white); border:1px solid #ddd; }
    .btn:hover { transform:translateY(-2px); }

    /* ---------- Shared Sections ---------- */
    section { padding:50px 0; }
    .section-title { text-align:center; margin-bottom:40px; }
    .section-title h2 { font-size:2rem; margin:0 0 8px; }
    .section-title p { color:var(--muted); max-width:600px; margin:auto; }

    .grid { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; }
    .card { background:var(--white); border:1px solid #eee; border-radius:var(--radius); padding:20px; box-shadow:var(--shadow); text-align:center; }
    .card .icon { font-size:2.5rem; margin-bottom:15px; color:var(--accent); }
    .card h3 { margin:0 0 10px; }
    .card p { color:var(--muted); line-height:1.6; }

    .how-it-works-grid { display:grid; grid-template-columns:1fr 1fr; gap:40px; align-items:center; }
    .how-it-works-grid img { border-radius:var(--radius); box-shadow:var(--shadow); }
    .step { display:flex; align-items:flex-start; gap:15px; margin-bottom:20px; }
    .step-number { background-color:var(--accent); color:white; min-width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold; }

    .testimonial { background-color:var(--cream); padding:20px; border-radius:var(--radius); border-left:5px solid var(--accent); }
    .testimonial blockquote { margin:0; font-style:italic; color:var(--muted); }
    .testimonial footer { text-align:right; font-weight:bold; margin-top:10px; }

    .cta-section { background-color:var(--cream); text-align:center; padding:50px 20px; border-radius:var(--radius); }

    footer { padding:40px 20px; text-align:center; color:var(--muted); border-top:1px solid #eee; }

    /* Button styling for header */
    .header-btn {
        background-color: var(--blue);
        color: white;
        padding: 10px 20px;
        border-radius: var(--radius);
        font-weight: bold;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .header-btn:hover {
        background-color: var(--blue-dark);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    .header-btn-logout {
        background-color: #ef4444;
        color: white;
        padding: 10px 20px;
        border-radius: var(--radius);
        font-weight: bold;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .header-btn-logout:hover {
        background-color: #dc2626;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    /* Responsive */
    @media (max-width:768px) {
        .grid, .how-it-works-grid { grid-template-columns:1fr; }
        .hero h1 { font-size:2rem; }
    }
  </style>
</head>
<body>

<header>
  <div class="nav-bar container">
    <a href="index.php" style="font-size:1.2rem;">Home</a>

    <nav style="display:flex; align-items:center; gap:20px;">
      <?php if ($loggedIn): ?>
        <a href="logout.php" class="header-btn-logout">Logout</a>
        <div class="profile-wrapper">
          <div class="user-profile-icon" id="userProfileIcon">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
          </div>
          <div class="menu-container-user" id="userMenuContainer"></div>
        </div>
      <?php else: ?>
        <a href="login.html" class="header-btn">Login</a>
        <a href="register.html" class="header-btn">Register</a>
        <button type="button" aria-label="Menu" id="menuButton">☰</button>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main>
    <section class="hero">
        <div class="container">
            <h1>A Better Way to Live, A Better Way to Give.</h1>
            <p>Welcome to a community platform designed to help you organize your life, improve your well‑being, and make a meaningful impact through simple, transparent actions.</p>
            <div>
                <a href="<?php echo $loggedIn ? 'dashboard.php' : 'register.html'; ?>" class="btn btn-primary">
                    <?php echo $loggedIn ? 'Go to Dashboard' : 'Join Our Community'; ?>
                </a>
                <a href="services.html" class="btn btn-secondary">Explore Services</a>
            </div>
        </div>
    </section>

    <!-- (the rest of your page – unchanged) -->
    <section class="container">
        <div class="section-title">
            <h2>What We Offer</h2>
            <p>Everything you need to support your well‑being and contribute to the community, all in one place.</p>
        </div>
        <div class="grid">
            <div class="card"><div class="icon">❤️</div><h3>Donate Securely</h3><p>Support causes you care about with our easy and transparent donation system. See the impact of your generosity through real stories and updates.</p></div>
            <div class="card"><div class="icon">🩸</div><h3>Give Blood, Save Lives</h3><p>Find local blood donation camps, read inspiring articles from fellow donors, and book your appointment in just a few clicks.</p></div>
            <div class="card"><div class="icon">🧘</div><h3>Find Your Balance</h3><p>Access simple, guided yoga and wellness routines designed to fit into your daily life. Nurture your mind and body, one session at a time.</p></div>
        </div>
    </section>

    <section style="background-color:var(--cream);">
        <div class="container how-it-works-grid">
            <div>
                <div class="section-title" style="text-align:left; margin-bottom:30px;">
                    <h2>How It Works</h2>
                    <p>Getting started is simple, secure, and rewarding.</p>
                </div>
                <div class="step"><div class="step-number">1</div><div><h3>Create Your Free Account</h3><p>Register in seconds to unlock all features, from booking appointments to tracking your contributions in a personal dashboard.</p></div></div>
                <div class="step"><div class="step-number">2</div><div><h3>Explore and Engage</h3><p>Browse our services, read articles, and find the right way for you to get involved or improve your daily routine.</p></div></div>
                <div class="step"><div class="step-number">3</div><div><h3>Make an Impact</h3><p>Whether you're donating money, giving blood, or investing in your own well‑being, every action contributes to a stronger community.</p></div></div>
            </div>
            <div><img src="https://images.unsplash.com/photo-1593113598332-cd288d649433?ixlib=rb-4.0.3&auto=format&fit=crop&w=800" alt="Person volunteering at a community event."></div>
        </div>
    </section>

    <section class="container">
        <div class="section-title"><h2>What Our Community Says</h2></div>
        <div class="grid">
            <div class="testimonial"><blockquote>"Booking my blood donation appointment was so easy! The whole process was seamless, and it felt great to contribute. This platform makes it simple to do good."</blockquote><footer>- Priya S.</footer></div>
            <div class="testimonial"><blockquote>"I love the yoga sessions. They're short, easy to follow, and have genuinely helped me de‑stress during my workday. It's my little pocket of peace."</blockquote><footer>- Rohan M.</footer></div>
            <div class="testimonial"><blockquote>"It's refreshing to see exactly where my donation is going. The impact stories are inspiring and give me confidence that my support matters."</blockquote><footer>- Anjali K.</footer></div>
        </div>
    </section>

    <section class="container">
        <div class="cta-section">
            <h2>Ready to Get Started?</h2>
            <p>Create your account today to join a community dedicated to positive change.</p>
            <br>
            <a href="<?php echo $loggedIn ? 'dashboard.php' : 'register.html'; ?>" class="btn btn-primary">
                <?php echo $loggedIn ? 'Go to Dashboard' : 'Sign Up for Free'; ?>
            </a>
        </div>
    </section>
</main>

<footer>
  <p class="email">123@gmail.com | 121351651351</p>
  <p>&copy; <?php echo date('Y'); ?> MyWebsite. All Rights Reserved.</p>
</footer>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const isLoggedIn = <?php echo $loggedIn ? 'true' : 'false'; ?>;
  const isAdmin    = <?php echo $isAdmin ? 'true' : 'false'; ?>;
  const userName   = "<?php echo $userName; ?>";   // <-- name fetched from PHP

  const items = [
    { name: 'Donate',         link: isLoggedIn ? 'donate.php' : 'login.html' },
    { name: 'Blood Donation', link: isLoggedIn ? 'blooddonate.php' : 'login.html' },
    { name: 'Yoga',           link: isLoggedIn ? 'yoga.html' : 'login.html' },
    { name: 'Services',       link: 'services.html' },
    { name: 'Contact Us',     link: 'contact.html' }
  ];
  if (isAdmin) items.push({ name: 'Admin Dashboard', link: 'admin_dashboard.php' });

  if (isLoggedIn) {
    const userMenuContainer = document.getElementById('userMenuContainer');
    const userProfileIcon   = document.getElementById('userProfileIcon');
    const menuList          = document.createElement('ul');

    /* ---- Greeting (first item) ---- */
    const greetLi = document.createElement('li');
    greetLi.style.pointerEvents = 'none';               // make it non-clickable
    greetLi.style.fontWeight    = '600';
    greetLi.style.color         = '#555';
    greetLi.textContent = `Welcome, ${userName}`;
    menuList.appendChild(greetLi);

    /* ---- Regular menu items ---- */
    items.forEach(it => {
      const li = document.createElement('li');
      const a  = document.createElement('a');
      a.href = it.link;
      a.textContent = it.name;
      li.appendChild(a);
      menuList.appendChild(li);
    });

    userMenuContainer.appendChild(menuList);

    userProfileIcon.addEventListener('click', (e) => {
      e.stopPropagation();
      userMenuContainer.classList.toggle('show');
    });

    // Close when clicking outside
    document.addEventListener('click', (e) => {
      if (!userProfileIcon.contains(e.target) && !userMenuContainer.contains(e.target)) {
        userMenuContainer.classList.remove('show');
      }
    });
  } else {
    /* ---- Non-logged-in menu (hamburger) ---- */
    const menuButton   = document.getElementById('menuButton');
    const menuContainer = document.createElement('div');
    menuContainer.className = 'menu-container';
    const menuList = document.createElement('ul');

    items.forEach(it => {
      const li = document.createElement('li');
      const a  = document.createElement('a');
      a.href = it.link;
      a.textContent = it.name;
      li.appendChild(a);
      menuList.appendChild(li);
    });

    menuContainer.appendChild(menuList);
    document.body.appendChild(menuContainer);

    menuButton.addEventListener('click', (e) => {
      e.stopPropagation();
      menuContainer.classList.toggle('show');
    });

    document.addEventListener('click', (e) => {
      if (!menuButton.contains(e.target) && !menuContainer.contains(e.target)) {
        menuContainer.classList.remove('show');
      }
    });
  }
});
</script>
</body>
</html> 

// Close when clicking outside
