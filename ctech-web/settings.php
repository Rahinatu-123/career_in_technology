<?php
// Start session
session_start();

// Check if user is logged in
if(!isset($_SESSION["user_id"])){
    header("location: login.php");
    exit;
}

// Include database configuration
require_once 'config.php';

// Get user information
$user_id = $_SESSION["user_id"];
$email = $_SESSION["email"];
$full_name = $_SESSION["full_name"];
$role = $_SESSION["role"];
$username = $email; // Use email as username since that's what we have in the session

// Process form submission
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Handle profile update
    if(isset($_POST['update_profile'])) {
        $username = trim($_POST["username"]);
        $email = trim($_POST["email"]);
        $current_password = trim($_POST["current_password"]);
        $new_password = trim($_POST["new_password"]);
        $confirm_password = trim($_POST["confirm_password"]);
        
        // Validate current password
        $sql = "SELECT password FROM users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user_data = mysqli_fetch_assoc($result);
        
        if(password_verify($current_password, $user_data['password'])) {
            // Update profile
            $sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssi", $username, $email, $user_id);
            
            if(mysqli_stmt_execute($stmt)) {
                // Update password if provided
                if(!empty($new_password)) {
                    if($new_password == $confirm_password) {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $sql = "UPDATE users SET password = ? WHERE id = ?";
                        $stmt = mysqli_prepare($conn, $sql);
                        mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);
                        mysqli_stmt_execute($stmt);
                        $success_message = "Profile and password updated successfully.";
                    } else {
                        $error_message = "New passwords do not match.";
                    }
                } else {
                    $success_message = "Profile updated successfully.";
                }
            } else {
                $error_message = "Error updating profile.";
            }
        } else {
            $error_message = "Current password is incorrect.";
        }
    }
    
    // Handle site settings update
    if(isset($_POST['update_site_settings'])) {
        $site_name = trim($_POST["site_name"]);
        $site_description = trim($_POST["site_description"]);
        $contact_email = trim($_POST["contact_email"]);
        $items_per_page = trim($_POST["items_per_page"]);
        
        // Update settings in the database
        $settings = [
            'site_name' => $site_name,
            'site_description' => $site_description,
            'contact_email' => $contact_email,
            'items_per_page' => $items_per_page
        ];
        
        $success = true;
        foreach ($settings as $key => $value) {
            $sql = "UPDATE settings SET setting_value = ? WHERE setting_key = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $value, $key);
            if (!mysqli_stmt_execute($stmt)) {
                $success = false;
                break;
            }
        }
        
        if ($success) {
            $success_message = "Site settings updated successfully.";
        } else {
            $error_message = "Error updating site settings.";
        }
    }
}

// Get current settings from the database
$settings = [];
$sql = "SELECT setting_key, setting_value FROM settings";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Set default values if settings don't exist
$site_name = isset($settings['site_name']) ? $settings['site_name'] : 'CTech Admin';
$site_description = isset($settings['site_description']) ? $settings['site_description'] : 'Career Technology Admin Panel';
$contact_email = isset($settings['contact_email']) ? $settings['contact_email'] : 'admin@ctech.com';
$items_per_page = isset($settings['items_per_page']) ? $settings['items_per_page'] : 10;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - CTech Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            overflow: hidden;
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
        .settings-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-right: 15px;
            color: #fff;
        }
        .profile-icon {
            background-color: #4e73df;
        }
        .site-icon {
            background-color: #1cc88a;
        }
        .database-icon {
            background-color: #f6c23e;
        }
        .settings-section {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .settings-section h5 {
            margin: 0;
        }
        .form-label {
            font-weight: 500;
        }
        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
        }
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2653d4;
        }
        .btn-success {
            background-color: #1cc88a;
            border-color: #1cc88a;
        }
        .btn-success:hover {
            background-color: #17a673;
            border-color: #169b6b;
        }
        .btn-warning {
            background-color: #f6c23e;
            border-color: #f6c23e;
        }
        .btn-warning:hover {
            background-color: #f4b619;
            border-color: #f4b30d;
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
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="admin_careers.php"><i class="fas fa-briefcase"></i> Careers</a></li>
                <li><a href="stories.php"><i class="fas fa-book-open"></i> Inspiring Stories</a></li>
                <li><a href="admin_tech_words.php"><i class="fas fa-language"></i> Tech Words</a></li>
                <li><a href="quiz.php"><i class="fas fa-question-circle"></i> Career Quiz</a></li>
                <li><a href="app_users.php"><i class="fas fa-users"></i> App Users</a></li>
                <li><a href="analytics.php"><i class="fas fa-chart-bar"></i> Analytics</a></li>
                <li><a href="settings.php" class="active"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <h4>Settings</h4>
            <div class="user-info">
                <span class="me-3"><?php echo htmlspecialchars($full_name); ?></span>
                <a href="logout.php" class="btn btn-sm btn-outline-danger">Logout</a>
            </div>
        </div>

        <?php if(isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if(isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Profile Settings -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <div class="settings-section">
                            <div class="settings-icon profile-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <h5>Profile Settings</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password (leave blank to keep current)</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Site Settings -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <div class="settings-section">
                            <div class="settings-icon site-icon">
                                <i class="fas fa-globe"></i>
                            </div>
                            <h5>Site Settings</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="mb-3">
                                <label for="site_name" class="form-label">Site Name</label>
                                <input type="text" class="form-control" id="site_name" name="site_name" value="<?php echo htmlspecialchars($site_name); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="site_description" class="form-label">Site Description</label>
                                <textarea class="form-control" id="site_description" name="site_description" rows="3" required><?php echo htmlspecialchars($site_description); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="contact_email" class="form-label">Contact Email</label>
                                <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?php echo htmlspecialchars($contact_email); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="items_per_page" class="form-label">Items Per Page</label>
                                <input type="number" class="form-control" id="items_per_page" name="items_per_page" value="<?php echo htmlspecialchars($items_per_page); ?>" min="5" max="100" required>
                            </div>
                            <button type="submit" name="update_site_settings" class="btn btn-success">Update Site Settings</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Database Settings -->
        <div class="card">
            <div class="card-header">
                <div class="settings-section">
                    <div class="settings-icon database-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <h5>Database Settings</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3">Database Information</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Server</th>
                                    <td><?php echo DB_SERVER; ?></td>
                                </tr>
                                <tr>
                                    <th>Port</th>
                                    <td><?php echo DB_PORT; ?></td>
                                </tr>
                                <tr>
                                    <th>Database</th>
                                    <td><?php echo DB_NAME; ?></td>
                                </tr>
                                <tr>
                                    <th>Username</th>
                                    <td><?php echo DB_USERNAME; ?></td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td><span class="badge bg-success">Connected</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3">Database Actions</h6>
                        <div class="d-grid gap-2">
                            <a href="backup.php" class="btn btn-warning">
                                <i class="fas fa-download me-2"></i> Backup Database
                            </a>
                            <a href="../../db/execute_sql.php" class="btn btn-info" target="_blank">
                                <i class="fas fa-sync-alt me-2"></i> Rebuild Database
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
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