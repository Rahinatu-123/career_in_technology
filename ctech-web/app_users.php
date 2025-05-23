<?php
session_start();
require_once 'config.php';

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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';
                $firstname = $_POST['firstname'] ?? '';
                $lastname = $_POST['lastname'] ?? '';
                $role = $_POST['role'] ?? 'user';
                
                if (!empty($email) && !empty($password) && !empty($firstname) && !empty($lastname)) {
                    // Hash the password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert new user
                    $stmt = $conn->prepare("INSERT INTO users (email, password, firstname, lastname, role) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssss", $email, $hashed_password, $firstname, $lastname, $role);
                    
                    if ($stmt->execute()) {
                        $success_message = "User created successfully!";
                    } else {
                        $error_message = "Error creating user: " . $conn->error;
                    }
                } else {
                    $error_message = "All fields are required!";
                }
                break;
                
            case 'delete':
                $id = $_POST['id'] ?? 0;
                
                if ($id > 0) {
                    // Delete user
                    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    
                    if ($stmt->execute()) {
                        $success_message = "User deleted successfully!";
                    } else {
                        $error_message = "Error deleting user: " . $conn->error;
                    }
                }
                break;
        }
    }
}

// Get all users
$app_users = [];
$result = $conn->query("
    SELECT id, email, firstname, lastname, created_at 
    FROM users 
    ORDER BY created_at DESC
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $app_users[] = $row;
    }
}

// Get user details for viewing if ID is provided
$view_user = null;
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $id = (int)$_GET['view'];
    
    // Get user details
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $view_user = $result->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTech Admin - App Users</title>
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
        .table th {
            font-weight: 600;
            background-color: #f8f9fa;
        }
        .action-buttons .btn {
            margin-right: 5px;
        }
        .favorite-count {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .favorite-item {
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
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
        .create-user-btn {
            margin-bottom: 20px;
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
                <li><a href="app_users.php" class="active"><i class="fas fa-users"></i> App Users</a></li>
                <li><a href="analytics.php"><i class="fas fa-chart-bar"></i> Analytics</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col">
                    <h2>App Users</h2>
                </div>
                <div class="col text-end">
                    <button type="button" class="btn btn-primary create-user-btn" data-bs-toggle="modal" data-bs-target="#createUserModal">
                        <i class="fas fa-plus"></i> Create User
                    </button>
                </div>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <!-- Top Bar -->
            <div class="top-bar">
                <h4>App Users</h4>
                <div class="user-info">
                    <span class="me-3"><?php echo htmlspecialchars($full_name); ?></span>
                    <a href="logout.php" class="btn btn-sm btn-outline-danger">Logout</a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Mobile App Users</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Language</th>
                                            <th>Joined</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($app_users)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center">No app users found.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($app_users as $user): ?>
                                                <tr>
                                                    <td><?php echo $user['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></td>
                                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                    <td><?php echo isset($user['preferred_language']) ? htmlspecialchars($user['preferred_language']) : 'Not set'; ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                                    <td class="action-buttons">
                                                        <a href="?view=<?php echo $user['id']; ?>" class="btn btn-sm btn-info">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal<?php echo $user['id']; ?>">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View User Modal -->
    <?php if ($view_user): ?>
    <div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewUserModalLabel">User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Basic Information</h6>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($view_user['first_name'] . ' ' . $view_user['last_name']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($view_user['email']); ?></p>
                            <p><strong>Language:</strong> <?php echo htmlspecialchars($view_user['preferred_language']); ?></p>
                            <p><strong>Joined:</strong> <?php echo date('M d, Y', strtotime($view_user['created_at'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Preferences</h6>
                            <p><strong>Notifications:</strong> <?php echo $view_user['notification_preferences'] ? 'Enabled' : 'Disabled'; ?></p>
                            <p><strong>Theme:</strong> <?php echo htmlspecialchars($view_user['theme_preference']); ?></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Delete User Modals -->
    <?php foreach ($app_users as $user): ?>
    <div class="modal fade" id="deleteUserModal<?php echo $user['id']; ?>" tabindex="-1" aria-labelledby="deleteUserModalLabel<?php echo $user['id']; ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteUserModalLabel<?php echo $user['id']; ?>">Delete User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this user: <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Create User Modal -->
    <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createUserModalLabel">Create New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="firstname" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstname" name="firstname" required>
                        </div>
                        <div class="mb-3">
                            <label for="lastname" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastname" name="lastname" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Create User</button>
                    </div>
                </form>
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

            // Show view user modal if user is being viewed
            <?php if ($view_user): ?>
            new bootstrap.Modal(document.getElementById('viewUserModal')).show();
            <?php endif; ?>
        });
    </script>
</body>
</html> 