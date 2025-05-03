<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Get user information
$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'];
$full_name = $_SESSION['full_name'];
$role = $_SESSION['role'];

// Get dashboard statistics
$stats = [];

// Total careers
$result = $conn->query("SELECT COUNT(*) as count FROM career_profiles");
$stats['careers'] = $result->fetch_assoc()['count'];

// Total stories
$result = $conn->query("SELECT COUNT(*) as count FROM inspiring_stories");
$stats['stories'] = $result->fetch_assoc()['count'];

// Total tech words
$result = $conn->query("SELECT COUNT(*) as count FROM tech_words");
$stats['tech_words'] = $result->fetch_assoc()['count'];

// Total app users
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$stats['app_users'] = $result->fetch_assoc()['count'];

// Get recent activities
$recent_activities = [];
$result = $conn->query("
    SELECT 'career' as type, title as name, created_at 
    FROM career_profiles 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    UNION ALL
    SELECT 'story' as type, name, created_at 
    FROM inspiring_stories 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    UNION ALL
    SELECT 'tech_word' as type, word as name, created_at 
    FROM tech_words 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ORDER BY created_at DESC
    LIMIT 10
");

while ($row = $result->fetch_assoc()) {
    $recent_activities[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTech Admin - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 250px;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background-color: #343a40;
            color: #fff;
            padding-top: 20px;
            z-index: 1000;
            transition: all 0.3s;
        }
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid #495057;
        }
        .sidebar-header h3 {
            color: #fff;
            font-size: 1.5rem;
            margin: 0;
        }
        .sidebar-menu {
            padding: 20px 0;
        }
        .sidebar-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        .sidebar-menu a {
            display: block;
            padding: 10px 20px;
            color: #adb5bd;
            text-decoration: none;
            transition: all 0.3s;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            color: #fff;
            background-color: #495057;
        }
        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: all 0.3s;
        }
        .top-bar {
            background-color: #fff;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .user-info {
            display: flex;
            align-items: center;
        }
        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #f1f1f1;
            padding: 15px 20px;
            font-weight: 600;
        }
        .card-body {
            padding: 20px;
        }
        .stat-card {
            text-align: center;
            padding: 20px;
        }
        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: #007bff;
        }
        .stat-card h3 {
            font-size: 2rem;
            margin-bottom: 5px;
        }
        .stat-card p {
            color: #6c757d;
            margin: 0;
        }
        .activity-item {
            padding: 10px 0;
            border-bottom: 1px solid #f1f1f1;
        }
        .activity-item:last-child {
            border-bottom: none;
        }
        .activity-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
        }
        .activity-career {
            background-color: #e3f2fd;
            color: #0d6efd;
        }
        .activity-story {
            background-color: #fce4ec;
            color: #e91e63;
        }
        .activity-word {
            background-color: #e8f5e9;
            color: #4caf50;
        }
        @media (max-width: 768px) {
            .sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
            }
            .sidebar.active {
                margin-left: 0;
            }
            .main-content {
                margin-left: 0;
            }
            .main-content.active {
                margin-left: var(--sidebar-width);
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>CTech Admin</h3>
        </div>
        <div class="sidebar-menu">
            <ul>
                <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="admin_careers.php"><i class="fas fa-briefcase"></i> Careers</a></li>
                <li><a href="stories.php"><i class="fas fa-book-open"></i> Inspiring Stories</a></li>
                <li><a href="admin_tech_words.php"><i class="fas fa-language"></i> Tech Words</a></li>
                <li><a href="quiz.php"><i class="fas fa-question-circle"></i> Career Quiz</a></li>
                <li><a href="app_users.php"><i class="fas fa-users"></i> App Users</a></li>
                <li><a href="analytics.php"><i class="fas fa-chart-bar"></i> Analytics</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <h4>Dashboard</h4>
            <div class="user-info">
                <span class="me-3"><?php echo htmlspecialchars($full_name); ?></span>
                <a href="logout.php" class="btn btn-sm btn-outline-danger">Logout</a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="card stat-card">
                    <i class="fas fa-briefcase"></i>
                    <h3><?php echo $stats['careers']; ?></h3>
                    <p>Total Careers</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <i class="fas fa-book-open"></i>
                    <h3><?php echo $stats['stories']; ?></h3>
                    <p>Inspiring Stories</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <i class="fas fa-language"></i>
                    <h3><?php echo $stats['tech_words']; ?></h3>
                    <p>Tech Words</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <i class="fas fa-users"></i>
                    <h3><?php echo $stats['app_users']; ?></h3>
                    <p>App Users</p>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        Recent Activities
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_activities)): ?>
                            <p class="text-muted">No recent activities</p>
                        <?php else: ?>
                            <?php foreach ($recent_activities as $activity): ?>
                                <div class="activity-item d-flex align-items-center">
                                    <div class="activity-icon activity-<?php echo $activity['type']; ?>">
                                        <?php if ($activity['type'] === 'career'): ?>
                                            <i class="fas fa-briefcase"></i>
                                        <?php elseif ($activity['type'] === 'story'): ?>
                                            <i class="fas fa-book-open"></i>
                                        <?php else: ?>
                                            <i class="fas fa-language"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($activity['name']); ?></strong>
                                        <div class="text-muted small">
                                            <?php echo date('M d, Y', strtotime($activity['created_at'])); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar on mobile
        document.addEventListener('DOMContentLoaded', function() {
            const toggleSidebar = document.createElement('button');
            toggleSidebar.className = 'btn btn-dark d-md-none position-fixed';
            toggleSidebar.style.top = '10px';
            toggleSidebar.style.left = '10px';
            toggleSidebar.style.zIndex = '1001';
            toggleSidebar.innerHTML = '<i class="fas fa-bars"></i>';
            
            document.body.appendChild(toggleSidebar);
            
            toggleSidebar.addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('active');
                document.querySelector('.main-content').classList.toggle('active');
            });
        });
    </script>
</body>
</html> 