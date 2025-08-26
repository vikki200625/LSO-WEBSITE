<?php
session_start();
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Include database configuration
require_once 'config.php';

// ✅ Create the database connection
$conn = db_connect();  // now $conn is defined and ready to use

// Fetch real-time data from database
try {
    // Get total users count
    $usersQuery = "SELECT COUNT(*) as total FROM users";
    $usersResult = $conn->query($usersQuery);
    $totalUsers = $usersResult->fetch_assoc()['total'];

    // Get total donations sum
    $donationsQuery = "SELECT SUM(amount) as total FROM donations";
    $donationsResult = $conn->query($donationsQuery);
    $totalDonations = $donationsResult->fetch_assoc()['total'] ?? 0;


    // Get total articles count
    $articlesQuery = "SELECT COUNT(*) as total FROM articles";
    $articlesResult = $conn->query($articlesQuery);
    $totalArticles = $articlesResult->fetch_assoc()['total'];

    // Get recent activities
    $activitiesQuery = "
        SELECT 'donation' as type, u.name as user_name, d.amount as details, d.created_at as time 
        FROM donations d 
        JOIN users u ON d.user_id = u.id 
        UNION ALL
        SELECT 'registration' as type, name as user_name, 'New user registered' as details, created_at as time 
        FROM users 
        UNION ALL
        SELECT 'article' as type, a.author_name as user_name, CONCAT('Published \"', a.title, '\"') as details, a.published_at as time 
        FROM articles a 
        ORDER BY time DESC 
        LIMIT 4
    ";
    $activitiesResult = $conn->query($activitiesQuery);
    $recentActivities = [];
    
    while ($row = $activitiesResult->fetch_assoc()) {
        $recentActivities[] = $row;
    }
    
} catch (Exception $e) {
    // Handle database errors
    $totalUsers = 0;
    $totalDonations = 0;
    $totalArticles = 0;
    $recentActivities = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MyWebsite</title>
    <style>
        :root {
            --cream: #fdfaf5;
            --white: #ffffff;
            --text: #333333;
            --muted: #6b7280;
            --accent: #d4a373;
            --accent-dark: #b78147;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --radius: 10px;
            --blue: #3b82f6;
            --blue-dark: #2563eb;
            --green: #10b981;
            --red: #ef4444;
            --purple: #8b5cf6;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #fdfaf5 0%, #ffffff 100%);
            min-height: 100vh;
            color: var(--text);
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        .header {
            background: var(--white);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--accent);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-name {
            font-weight: 600;
            color: var(--text);
        }

        .logout-btn {
            background: var(--red);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }

        /* Dashboard Layout */
        .dashboard {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 30px;
            padding: 30px 0;
        }

        /* Sidebar Styles */
        .sidebar {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 25px;
            height: fit-content;
            position: sticky;
            top: 100px;
        }

        .sidebar-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 5px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            border-radius: 8px;
            color: var(--text);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .sidebar-menu a:hover {
            background: var(--cream);
            color: var(--accent);
            transform: translateX(5px);
        }

        .sidebar-menu a.active {
            background: var(--accent);
            color: white;
        }

        /* Main Content Styles */
        .main-content {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        /* Welcome Section */
        .welcome-section {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 30px;
        }

        .welcome-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 10px;
        }

        .welcome-subtitle {
            color: var(--muted);
            font-size: 1.1rem;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
        }

        .stat-card {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 25px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: var(--accent);
        }

        .stat-card.users::before { background: var(--blue); }
        .stat-card.donations::before { background: var(--green); }
        .stat-card.articles::before { background: var(--purple); }
        .stat-card.activities::before { background: var(--red); }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .stat-title {
            font-weight: 600;
            color: var(--muted);
            font-size: 0.9rem;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .stat-card.users .stat-icon { background: rgba(59, 130, 246, 0.1); color: var(--blue); }
        .stat-card.donations .stat-icon { background: rgba(16, 185, 129, 0.1); color: var(--green); }
        .stat-card.articles .stat-icon { background: rgba(139, 92, 246, 0.1); color: var(--purple); }
        .stat-card.activities .stat-icon { background: rgba(239, 68, 68, 0.1); color: var(--red); }

        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-change {
            font-size: 0.85rem;
            font-weight: 600;
        }

        .stat-change.positive { color: var(--green); }
        .stat-change.negative { color: var(--red); }

        /* Recent Activities */
        .recent-activities {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent);
        }

        .view-all-btn {
            background: var(--cream);
            color: var(--accent);
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .view-all-btn:hover {
            background: var(--accent);
            color: white;
        }

        .activities-table {
            width: 100%;
            border-collapse: collapse;
        }

        .activities-table th {
            text-align: left;
            padding: 12px 15px;
            background: var(--cream);
            font-weight: 600;
            color: var(--accent);
            border-bottom: 1px solid #e5e7eb;
        }

        .activities-table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        .activities-table tr:hover {
            background: var(--cream);
        }

        .activity-type {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .activity-type.donation { background: rgba(16, 185, 129, 0.1); color: var(--green); }
        .activity-type.registration { background: rgba(59, 130, 246, 0.1); color: var(--blue); }
        .activity-type.article { background: rgba(139, 92, 246, 0.1); color: var(--purple); }

        /* Quick Actions */
        .quick-actions {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 30px;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .action-card {
            background: var(--cream);
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .action-card:hover {
            background: var(--accent);
            color: white;
            transform: translateY(-5px);
        }

        .action-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .action-title {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .action-description {
            font-size: 0.9rem;
            color: inherit;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .dashboard {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                position: static;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .actions-grid {
                grid-template-columns: 1fr;
            }
            
            .activities-table {
                font-size: 0.9rem;
            }
            
            .activities-table th,
            .activities-table td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">MyWebsite Admin</div>
                <div class="user-info">
                    <span class="user-name">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
                    <a href="admin_logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="dashboard">
            <!-- Sidebar -->
            <aside class="sidebar">
                <h2 class="sidebar-title">Admin Menu</h2>
                <ul class="sidebar-menu">
                    <li><a href="admin_dashboard.php" class="active">📊 Dashboard</a></li>
                    <li><a href="admin_articles.php">📝 Manage Articles</a></li>
                    <li><a href="admin_donation_content.php">💰 Donation Content</a></li>
                    <li><a href="admin_users.php">👥 Manage Users</a></li>
                    <li><a href="admin_donations.php">💸 Manage Donations</a></li>
                    <li><a href="admin_blood_donations.php">🩸 Blood Donations</a></li>
                    <li><a href="admin_reports.php">📈 Reports</a></li>
                </ul>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
                <!-- Welcome Section -->
                <section class="welcome-section">
                    <h1 class="welcome-title">Admin Dashboard</h1>
                    <p class="welcome-subtitle">Welcome back! Here's what's happening with your platform today.</p>
                </section>

                <!-- Stats Cards -->
                <section class="stats-grid">
                    <div class="stat-card users">
                        <div class="stat-header">
                            <span class="stat-title">Total Users</span>
                            <div class="stat-icon">👥</div>
                        </div>
                        <div class="stat-value"><?php echo number_format($totalUsers); ?></div>
                        <div class="stat-change positive">↑ Real-time data</div>
                    </div>

                    <div class="stat-card donations">
                        <div class="stat-header">
                            <span class="stat-title">Total Donations</span>
                            <div class="stat-icon">💰</div>
                        </div>
                        <div class="stat-value">$<?php echo number_format($totalDonations, 2); ?></div>
                        <div class="stat-change positive">↑ Real-time data</div>
                    </div>

                    <div class="stat-card articles">
                        <div class="stat-header">
                            <span class="stat-title">Articles</span>
                            <div class="stat-icon">📝</div>
                        </div>
                        <div class="stat-value"><?php echo number_format($totalArticles); ?></div>
                        <div class="stat-change positive">↑ Real-time data</div>
                    </div>

                    <div class="stat-card activities">
                        <div class="stat-header">
                            <span class="stat-title">Activities</span>
                            <div class="stat-icon">📊</div>
                        </div>
                        <div class="stat-value"><?php echo count($recentActivities); ?></div>
                        <div class="stat-change positive">↑ Real-time data</div>
                    </div>
                </section>

                <!-- Recent Activities -->
                <section class="recent-activities">
                    <div class="section-header">
                        <h2 class="section-title">Recent Activities</h2>
                        <button class="view-all-btn" onclick="window.location.href='admin_activities.php'">View All</button>
                    </div>
                    <table class="activities-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Action</th>
                                <th>Details</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentActivities)): ?>
                                <?php foreach ($recentActivities as $activity): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($activity['user_name']); ?></td>
                                        <td><span class="activity-type <?php echo $activity['type']; ?>"><?php echo ucfirst($activity['type']); ?></span></td>
                                        <td><?php echo htmlspecialchars($activity['details']); ?></td>
                                        <td><?php echo date('M j, Y g:i A', strtotime($activity['time'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 20px;">No recent activities found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </section>

                <!-- Quick Actions -->
                <section class="quick-actions">
                    <div class="section-header">
                        <h2 class="section-title">Quick Actions</h2>
                    </div>
                    <div class="actions-grid">
                        <div class="action-card" onclick="window.location.href='admin_articles.php'">
                            <div class="action-icon">📝</div>
                            <div class="action-title">Add Article</div>
                            <div class="action-description">Create a new article</div>
                        </div>
                        <div class="action-card" onclick="window.location.href='admin_users.php'">
                            <div class="action-icon">👥</div>
                            <div class="action-title">Add User</div>
                            <div class="action-description">Create a new user</div>
                        </div>
                        <div class="action-card" onclick="window.location.href='admin_donation_content.php'">
                            <div class="action-icon">💰</div>
                            <div class="action-title">Update Content</div>
                            <div class="action-description">Edit donation page</div>
                        </div>
                        <div class="action-card" onclick="window.location.href='admin_reports.php'">
                            <div class="action-icon">📈</div>
                            <div class="action-title">View Reports</div>
                            <div class="action-description">Analytics dashboard</div>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>
</body>
</html>