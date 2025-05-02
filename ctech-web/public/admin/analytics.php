<?php
session_start();
require_once '../../db/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get user information
$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'];
$full_name = $_SESSION['full_name'];
$role = $_SESSION['role'];

// Get date range filter
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Get overall statistics
$stats = [];

// Total users
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM app_users");
$stmt->execute();
$result = $stmt->get_result();
$stats['total_users'] = $result->fetch_assoc()['total'];

// New users in date range
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM app_users WHERE created_at BETWEEN ? AND ?");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();
$stats['new_users'] = $result->fetch_assoc()['total'];

// Total favorites
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM user_favorites");
$stmt->execute();
$result = $stmt->get_result();
$stats['total_favorites'] = $result->fetch_assoc()['total'];

// Favorites by type
$stmt = $conn->prepare("
    SELECT type, COUNT(*) as count 
    FROM user_favorites 
    GROUP BY type
");
$stmt->execute();
$result = $stmt->get_result();
$stats['favorites_by_type'] = [];
while ($row = $result->fetch_assoc()) {
    $stats['favorites_by_type'][$row['type']] = $row['count'];
}

// Get popular careers
$stmt = $conn->prepare("
    SELECT c.id, c.title, COUNT(f.id) as favorite_count
    FROM career_profiles c
    LEFT JOIN user_favorites f ON c.id = f.item_id AND f.type = 'career'
    GROUP BY c.id
    ORDER BY favorite_count DESC
    LIMIT 10
");
$stmt->execute();
$result = $stmt->get_result();
$popular_careers = [];
while ($row = $result->fetch_assoc()) {
    $popular_careers[] = $row;
}

// Get popular stories
$stmt = $conn->prepare("
    SELECT s.id, s.name, s.role, s.company, COUNT(f.id) as favorite_count
    FROM inspiring_stories s
    LEFT JOIN user_favorites f ON s.id = f.item_id AND f.type = 'story'
    GROUP BY s.id
    ORDER BY favorite_count DESC
    LIMIT 10
");
$stmt->execute();
$result = $stmt->get_result();
$popular_stories = [];
while ($row = $result->fetch_assoc()) {
    $popular_stories[] = $row;
}

// Get popular tech words
$stmt = $conn->prepare("
    SELECT w.id, w.word, w.category, COUNT(f.id) as favorite_count
    FROM tech_words w
    LEFT JOIN user_favorites f ON w.id = f.item_id AND f.type = 'word'
    GROUP BY w.id
    ORDER BY favorite_count DESC
    LIMIT 10
");
$stmt->execute();
$result = $stmt->get_result();
$popular_words = [];
while ($row = $result->fetch_assoc()) {
    $popular_words[] = $row;
}

// Get user growth data for chart
$stmt = $conn->prepare("
    SELECT DATE(created_at) as date, COUNT(*) as count
    FROM app_users
    WHERE created_at BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY date
");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();
$user_growth = [];
while ($row = $result->fetch_assoc()) {
    $user_growth[] = $row;
}

// Get favorite trends data for chart
$stmt = $conn->prepare("
    SELECT DATE(created_at) as date, type, COUNT(*) as count
    FROM user_favorites
    WHERE created_at BETWEEN ? AND ?
    GROUP BY DATE(created_at), type
    ORDER BY date, type
");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();
$favorite_trends = [];
while ($row = $result->fetch_assoc()) {
    $favorite_trends[] = $row;
}

// Get category distribution for tech words
$stmt = $conn->prepare("
    SELECT category, COUNT(*) as count
    FROM tech_words
    GROUP BY category
    ORDER BY count DESC
");
$stmt->execute();
$result = $stmt->get_result();
$word_categories = [];
while ($row = $result->fetch_assoc()) {
    $word_categories[] = $row;
}

// Get career category distribution
$stmt = $conn->prepare("
    SELECT c.name as category, COUNT(cr.id) as count
    FROM career_categories c
    LEFT JOIN careers cr ON c.id = cr.category_id
    GROUP BY c.id
    ORDER BY count DESC
");
$stmt->execute();
$result = $stmt->get_result();
$career_categories = [];
while ($row = $result->fetch_assoc()) {
    $career_categories[] = $row;
}

// Get user activity by hour
$stmt = $conn->prepare("
    SELECT HOUR(created_at) as hour, COUNT(*) as count
    FROM user_favorites
    WHERE created_at BETWEEN ? AND ?
    GROUP BY HOUR(created_at)
    ORDER BY hour
");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();
$activity_by_hour = [];
while ($row = $result->fetch_assoc()) {
    $activity_by_hour[] = $row;
}

// Get user retention data (users who favorited multiple items)
$stmt = $conn->prepare("
    SELECT user_id, COUNT(DISTINCT type) as type_count, COUNT(*) as total_favorites
    FROM user_favorites
    GROUP BY user_id
    HAVING total_favorites > 1
    ORDER BY total_favorites DESC
    LIMIT 100
");
$stmt->execute();
$result = $stmt->get_result();
$user_retention = [];
while ($row = $result->fetch_assoc()) {
    $user_retention[] = $row;
}

// Prepare data for charts
$user_growth_labels = [];
$user_growth_data = [];
foreach ($user_growth as $data) {
    $user_growth_labels[] = $data['date'];
    $user_growth_data[] = $data['count'];
}

$favorite_trends_data = [
    'career' => [],
    'story' => [],
    'word' => []
];
$favorite_trends_labels = [];

// Get unique dates
$dates = array_unique(array_column($favorite_trends, 'date'));
sort($dates);
$favorite_trends_labels = $dates;

// Initialize data arrays with zeros
foreach ($dates as $date) {
    $favorite_trends_data['career'][$date] = 0;
    $favorite_trends_data['story'][$date] = 0;
    $favorite_trends_data['word'][$date] = 0;
}

// Fill in actual data
foreach ($favorite_trends as $data) {
    $favorite_trends_data[$data['type']][$data['date']] = $data['count'];
}

// Convert to arrays for JavaScript
$favorite_trends_data['career'] = array_values($favorite_trends_data['career']);
$favorite_trends_data['story'] = array_values($favorite_trends_data['story']);
$favorite_trends_data['word'] = array_values($favorite_trends_data['word']);

// Prepare data for new charts
$word_category_labels = [];
$word_category_data = [];
foreach ($word_categories as $category) {
    $word_category_labels[] = $category['category'];
    $word_category_data[] = $category['count'];
}

$career_category_labels = [];
$career_category_data = [];
foreach ($career_categories as $category) {
    $career_category_labels[] = $category['category'];
    $career_category_data[] = $category['count'];
}

$activity_hour_labels = [];
$activity_hour_data = [];
foreach ($activity_by_hour as $hour) {
    $activity_hour_labels[] = $hour['hour'] . ':00';
    $activity_hour_data[] = $hour['count'];
}

// Calculate retention metrics
$retention_data = [
    '1-5' => 0,
    '6-10' => 0,
    '11-20' => 0,
    '21-50' => 0,
    '50+' => 0
];

foreach ($user_retention as $user) {
    if ($user['total_favorites'] <= 5) {
        $retention_data['1-5']++;
    } elseif ($user['total_favorites'] <= 10) {
        $retention_data['6-10']++;
    } elseif ($user['total_favorites'] <= 20) {
        $retention_data['11-20']++;
    } elseif ($user['total_favorites'] <= 50) {
        $retention_data['21-50']++;
    } else {
        $retention_data['50+']++;
    }
}

$retention_labels = array_keys($retention_data);
$retention_values = array_values($retention_data);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTech Admin - Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
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
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
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
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }
        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .stat-card .stat-label {
            font-size: 1rem;
            color: #6c757d;
        }
        .stat-card.primary {
            background-color: #e3f2fd;
            color: #0d6efd;
        }
        .stat-card.success {
            background-color: #e8f5e9;
            color: #198754;
        }
        .stat-card.warning {
            background-color: #fff3e0;
            color: #fd7e14;
        }
        .stat-card.info {
            background-color: #e0f7fa;
            color: #0dcaf0;
        }
        .popular-item {
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .popular-item .count {
            font-weight: 600;
            color: #0d6efd;
        }
        .date-filter {
            background-color: #fff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
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
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }
        
        .chart-legend {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 10px;
        }
        
        .chart-legend-item {
            display: flex;
            align-items: center;
            margin-right: 15px;
            margin-bottom: 5px;
        }
        
        .chart-legend-color {
            width: 15px;
            height: 15px;
            margin-right: 5px;
            border-radius: 3px;
        }
        
        .insight-card {
            background-color: #fff;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }
        
        .insight-title {
            font-weight: 600;
            margin-bottom: 10px;
            color: #495057;
        }
        
        .insight-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0d6efd;
        }
        
        .insight-description {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .tab-content {
            padding: 20px 0;
        }
        
        .nav-tabs .nav-link {
            color: #495057;
        }
        
        .nav-tabs .nav-link.active {
            font-weight: 600;
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
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="careers.php"><i class="fas fa-briefcase"></i> Careers</a></li>
                <li><a href="stories.php"><i class="fas fa-book-open"></i> Inspiring Stories</a></li>
                <li><a href="tech_words.php"><i class="fas fa-language"></i> Tech Words</a></li>
                <li><a href="quiz.php"><i class="fas fa-question-circle"></i> Career Quiz</a></li>
                <li><a href="app_users.php"><i class="fas fa-users"></i> App Users</a></li>
                <li><a href="analytics.php" class="active"><i class="fas fa-chart-bar"></i> Analytics</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <h4>Analytics Dashboard</h4>
            <div class="user-info">
                <span class="me-3"><?php echo htmlspecialchars($full_name); ?></span>
                <a href="logout.php" class="btn btn-sm btn-outline-danger">Logout</a>
            </div>
        </div>

        <!-- Date Filter -->
        <div class="date-filter">
            <form method="get" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">Apply Filter</button>
                </div>
            </form>
        </div>

        <!-- Key Metrics -->
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card primary">
                    <div class="stat-value"><?php echo $stats['total_users']; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card success">
                    <div class="stat-value"><?php echo $stats['new_users']; ?></div>
                    <div class="stat-label">New Users (<?php echo date('M d', strtotime($start_date)); ?> - <?php echo date('M d', strtotime($end_date)); ?>)</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card warning">
                    <div class="stat-value"><?php echo $stats['total_favorites']; ?></div>
                    <div class="stat-label">Total Favorites</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card info">
                    <div class="stat-value"><?php echo isset($stats['favorites_by_type']['career']) ? $stats['favorites_by_type']['career'] : 0; ?></div>
                    <div class="stat-label">Career Favorites</div>
                </div>
            </div>
        </div>

        <!-- Insights Section -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Key Insights</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="insight-card">
                                    <div class="insight-title">User Engagement Rate</div>
                                    <div class="insight-value">
                                        <?php 
                                        $engagement_rate = $stats['total_users'] > 0 ? 
                                            round(($stats['total_favorites'] / $stats['total_users']) * 100, 1) : 0;
                                        echo $engagement_rate . '%';
                                        ?>
                                    </div>
                                    <div class="insight-description">
                                        Average favorites per user
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="insight-card">
                                    <div class="insight-title">Most Popular Category</div>
                                    <div class="insight-value">
                                        <?php 
                                        $max_category = '';
                                        $max_count = 0;
                                        foreach ($career_categories as $category) {
                                            if ($category['count'] > $max_count) {
                                                $max_count = $category['count'];
                                                $max_category = $category['category'];
                                            }
                                        }
                                        echo $max_category ?: 'N/A';
                                        ?>
                                    </div>
                                    <div class="insight-description">
                                        Based on career profiles
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="insight-card">
                                    <div class="insight-title">Peak Activity Hour</div>
                                    <div class="insight-value">
                                        <?php 
                                        $peak_hour = '';
                                        $peak_count = 0;
                                        foreach ($activity_by_hour as $hour) {
                                            if ($hour['count'] > $peak_count) {
                                                $peak_count = $hour['count'];
                                                $peak_hour = $hour['hour'] . ':00';
                                            }
                                        }
                                        echo $peak_hour ?: 'N/A';
                                        ?>
                                    </div>
                                    <div class="insight-description">
                                        When users are most active
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="insight-card">
                                    <div class="insight-title">User Retention</div>
                                    <div class="insight-value">
                                        <?php 
                                        $high_retention = $retention_data['11-20'] + $retention_data['21-50'] + $retention_data['50+'];
                                        $retention_rate = count($user_retention) > 0 ? 
                                            round(($high_retention / count($user_retention)) * 100, 1) : 0;
                                        echo $retention_rate . '%';
                                        ?>
                                    </div>
                                    <div class="insight-description">
                                        Users with 11+ favorites
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Tabs -->
        <div class="card mt-4">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="analyticsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">Overview</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab" aria-controls="users" aria-selected="false">Users</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="content-tab" data-bs-toggle="tab" data-bs-target="#content" type="button" role="tab" aria-controls="content" aria-selected="false">Content</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="engagement-tab" data-bs-toggle="tab" data-bs-target="#engagement" type="button" role="tab" aria-controls="engagement" aria-selected="false">Engagement</button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="analyticsTabsContent">
                    <!-- Overview Tab -->
                    <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="chart-container">
                                    <canvas id="userGrowthChart"></canvas>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="chart-container">
                                    <canvas id="favoriteTrendsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Users Tab -->
                    <div class="tab-pane fade" id="users" role="tabpanel" aria-labelledby="users-tab">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="chart-container">
                                    <canvas id="userRetentionChart"></canvas>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="chart-container">
                                    <canvas id="activityByHourChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Content Tab -->
                    <div class="tab-pane fade" id="content" role="tabpanel" aria-labelledby="content-tab">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="chart-container">
                                    <canvas id="careerCategoriesChart"></canvas>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="chart-container">
                                    <canvas id="wordCategoriesChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Engagement Tab -->
                    <div class="tab-pane fade" id="engagement" role="tabpanel" aria-labelledby="engagement-tab">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="chart-container">
                                    <canvas id="engagementMetricsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Popular Content -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Popular Careers</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($popular_careers)): ?>
                            <p class="text-muted">No data available</p>
                        <?php else: ?>
                            <?php foreach ($popular_careers as $career): ?>
                                <div class="popular-item">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong><?php echo htmlspecialchars($career['title']); ?></strong>
                                        </div>
                                        <div class="count"><?php echo $career['favorite_count']; ?> favorites</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Popular Stories</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($popular_stories)): ?>
                            <p class="text-muted">No data available</p>
                        <?php else: ?>
                            <?php foreach ($popular_stories as $story): ?>
                                <div class="popular-item">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong><?php echo htmlspecialchars($story['name']); ?></strong>
                                            <div class="text-muted small"><?php echo htmlspecialchars($story['role']); ?> at <?php echo htmlspecialchars($story['company']); ?></div>
                                        </div>
                                        <div class="count"><?php echo $story['favorite_count']; ?> favorites</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Popular Tech Words</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($popular_words)): ?>
                            <p class="text-muted">No data available</p>
                        <?php else: ?>
                            <?php foreach ($popular_words as $word): ?>
                                <div class="popular-item">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong><?php echo htmlspecialchars($word['word']); ?></strong>
                                            <div class="text-muted small"><?php echo htmlspecialchars($word['category']); ?></div>
                                        </div>
                                        <div class="count"><?php echo $word['favorite_count']; ?> favorites</div>
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
        // Register Chart.js plugins
        Chart.register(ChartDataLabels);
        
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

            // Common chart options
            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    datalabels: {
                        color: '#333',
                        font: {
                            weight: 'bold'
                        },
                        formatter: function(value, context) {
                            if (value === 0) return '';
                            return value;
                        }
                    }
                }
            };

            // User Growth Chart
            const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
            new Chart(userGrowthCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($user_growth_labels); ?>,
                    datasets: [{
                        label: 'New Users',
                        data: <?php echo json_encode($user_growth_data); ?>,
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        borderColor: 'rgba(13, 110, 253, 1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    ...commonOptions,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });

            // Favorite Trends Chart
            const favoriteTrendsCtx = document.getElementById('favoriteTrendsChart').getContext('2d');
            new Chart(favoriteTrendsCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($favorite_trends_labels); ?>,
                    datasets: [
                        {
                            label: 'Careers',
                            data: <?php echo json_encode($favorite_trends_data['career']); ?>,
                            backgroundColor: 'rgba(13, 110, 253, 0.1)',
                            borderColor: 'rgba(13, 110, 253, 1)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true
                        },
                        {
                            label: 'Stories',
                            data: <?php echo json_encode($favorite_trends_data['story']); ?>,
                            backgroundColor: 'rgba(25, 135, 84, 0.1)',
                            borderColor: 'rgba(25, 135, 84, 1)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true
                        },
                        {
                            label: 'Tech Words',
                            data: <?php echo json_encode($favorite_trends_data['word']); ?>,
                            backgroundColor: 'rgba(253, 126, 20, 0.1)',
                            borderColor: 'rgba(253, 126, 20, 1)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true
                        }
                    ]
                },
                options: {
                    ...commonOptions,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
            
            // User Retention Chart
            const userRetentionCtx = document.getElementById('userRetentionChart').getContext('2d');
            new Chart(userRetentionCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($retention_labels); ?>,
                    datasets: [{
                        label: 'Number of Users',
                        data: <?php echo json_encode($retention_values); ?>,
                        backgroundColor: [
                            'rgba(13, 110, 253, 0.7)',
                            'rgba(25, 135, 84, 0.7)',
                            'rgba(253, 126, 20, 0.7)',
                            'rgba(220, 53, 69, 0.7)',
                            'rgba(111, 66, 193, 0.7)'
                        ],
                        borderColor: [
                            'rgba(13, 110, 253, 1)',
                            'rgba(25, 135, 84, 1)',
                            'rgba(253, 126, 20, 1)',
                            'rgba(220, 53, 69, 1)',
                            'rgba(111, 66, 193, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    ...commonOptions,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
            
            // Activity by Hour Chart
            const activityByHourCtx = document.getElementById('activityByHourChart').getContext('2d');
            new Chart(activityByHourCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($activity_hour_labels); ?>,
                    datasets: [{
                        label: 'User Activity',
                        data: <?php echo json_encode($activity_hour_data); ?>,
                        backgroundColor: 'rgba(13, 110, 253, 0.7)',
                        borderColor: 'rgba(13, 110, 253, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    ...commonOptions,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
            
            // Career Categories Chart
            const careerCategoriesCtx = document.getElementById('careerCategoriesChart').getContext('2d');
            new Chart(careerCategoriesCtx, {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode($career_category_labels); ?>,
                    datasets: [{
                        data: <?php echo json_encode($career_category_data); ?>,
                        backgroundColor: [
                            'rgba(13, 110, 253, 0.7)',
                            'rgba(25, 135, 84, 0.7)',
                            'rgba(253, 126, 20, 0.7)',
                            'rgba(220, 53, 69, 0.7)',
                            'rgba(111, 66, 193, 0.7)',
                            'rgba(13, 202, 240, 0.7)',
                            'rgba(255, 193, 7, 0.7)',
                            'rgba(108, 117, 125, 0.7)'
                        ],
                        borderColor: [
                            'rgba(13, 110, 253, 1)',
                            'rgba(25, 135, 84, 1)',
                            'rgba(253, 126, 20, 1)',
                            'rgba(220, 53, 69, 1)',
                            'rgba(111, 66, 193, 1)',
                            'rgba(13, 202, 240, 1)',
                            'rgba(255, 193, 7, 1)',
                            'rgba(108, 117, 125, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    ...commonOptions,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });
            
            // Word Categories Chart
            const wordCategoriesCtx = document.getElementById('wordCategoriesChart').getContext('2d');
            new Chart(wordCategoriesCtx, {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode($word_category_labels); ?>,
                    datasets: [{
                        data: <?php echo json_encode($word_category_data); ?>,
                        backgroundColor: [
                            'rgba(13, 110, 253, 0.7)',
                            'rgba(25, 135, 84, 0.7)',
                            'rgba(253, 126, 20, 0.7)',
                            'rgba(220, 53, 69, 0.7)',
                            'rgba(111, 66, 193, 0.7)',
                            'rgba(13, 202, 240, 0.7)',
                            'rgba(255, 193, 7, 0.7)',
                            'rgba(108, 117, 125, 0.7)'
                        ],
                        borderColor: [
                            'rgba(13, 110, 253, 1)',
                            'rgba(25, 135, 84, 1)',
                            'rgba(253, 126, 20, 1)',
                            'rgba(220, 53, 69, 1)',
                            'rgba(111, 66, 193, 1)',
                            'rgba(13, 202, 240, 1)',
                            'rgba(255, 193, 7, 1)',
                            'rgba(108, 117, 125, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    ...commonOptions,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });
            
            // Engagement Metrics Chart
            const engagementMetricsCtx = document.getElementById('engagementMetricsChart').getContext('2d');
            new Chart(engagementMetricsCtx, {
                type: 'bar',
                data: {
                    labels: ['Careers', 'Stories', 'Tech Words'],
                    datasets: [
                        {
                            label: 'Total Favorites',
                            data: [
                                <?php echo isset($stats['favorites_by_type']['career']) ? $stats['favorites_by_type']['career'] : 0; ?>,
                                <?php echo isset($stats['favorites_by_type']['story']) ? $stats['favorites_by_type']['story'] : 0; ?>,
                                <?php echo isset($stats['favorites_by_type']['word']) ? $stats['favorites_by_type']['word'] : 0; ?>
                            ],
                            backgroundColor: [
                                'rgba(13, 110, 253, 0.7)',
                                'rgba(25, 135, 84, 0.7)',
                                'rgba(253, 126, 20, 0.7)'
                            ],
                            borderColor: [
                                'rgba(13, 110, 253, 1)',
                                'rgba(25, 135, 84, 1)',
                                'rgba(253, 126, 20, 1)'
                            ],
                            borderWidth: 1
                        },
                        {
                            label: 'Favorites per Item',
                            data: [
                                <?php 
                                $career_count = isset($stats['favorites_by_type']['career']) ? $stats['favorites_by_type']['career'] : 0;
                                $story_count = isset($stats['favorites_by_type']['story']) ? $stats['favorites_by_type']['story'] : 0;
                                $word_count = isset($stats['favorites_by_type']['word']) ? $stats['favorites_by_type']['word'] : 0;
                                
                                $career_items = count($popular_careers);
                                $story_items = count($popular_stories);
                                $word_items = count($popular_words);
                                
                                echo $career_items > 0 ? round($career_count / $career_items, 1) : 0;
                                echo ', ';
                                echo $story_items > 0 ? round($story_count / $story_items, 1) : 0;
                                echo ', ';
                                echo $word_items > 0 ? round($word_count / $word_items, 1) : 0;
                                ?>
                            ],
                            backgroundColor: [
                                'rgba(13, 110, 253, 0.3)',
                                'rgba(25, 135, 84, 0.3)',
                                'rgba(253, 126, 20, 0.3)'
                            ],
                            borderColor: [
                                'rgba(13, 110, 253, 1)',
                                'rgba(25, 135, 84, 1)',
                                'rgba(253, 126, 20, 1)'
                            ],
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    ...commonOptions,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
</body>
</html> 