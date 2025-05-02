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

// Handle search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$careers = [];

if (!empty($search)) {
    $search_term = "%$search%";
    $stmt = $conn->prepare("SELECT * FROM career_profiles WHERE title LIKE ? OR description LIKE ? OR skills LIKE ? OR education LIKE ? OR salary_range LIKE ? OR job_outlook LIKE ? ORDER BY created_at DESC");
    $stmt->bind_param("ssssss", $search_term, $search_term, $search_term, $search_term, $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $careers[] = $row;
    }
} else {
    // Get all careers
    $result = $conn->query("SELECT * FROM career_profiles ORDER BY created_at DESC");
    while ($row = $result->fetch_assoc()) {
        $careers[] = $row;
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $title = $_POST['title'] ?? '';
                $description = $_POST['description'] ?? '';
                $skills = $_POST['skills'] ?? '';
                $education = $_POST['education'] ?? '';
                $salary_range = $_POST['salary_range'] ?? '';
                $job_outlook = $_POST['job_outlook'] ?? '';
                
                if (!empty($title) && !empty($description)) {
                    $stmt = $conn->prepare("INSERT INTO career_profiles (title, description, skills, education, salary_range, job_outlook) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssss", $title, $description, $skills, $education, $salary_range, $job_outlook);
                    
                    if ($stmt->execute()) {
                        $success_message = "Career profile added successfully!";
                    } else {
                        $error_message = "Error adding career profile: " . $conn->error;
                    }
                } else {
                    $error_message = "Title and description are required fields.";
                }
                break;
                
            case 'edit':
                $id = $_POST['id'] ?? 0;
                $title = $_POST['title'] ?? '';
                $description = $_POST['description'] ?? '';
                $skills = $_POST['skills'] ?? '';
                $education = $_POST['education'] ?? '';
                $salary_range = $_POST['salary_range'] ?? '';
                $job_outlook = $_POST['job_outlook'] ?? '';
                
                if ($id > 0 && !empty($title) && !empty($description)) {
                    $stmt = $conn->prepare("UPDATE career_profiles SET title = ?, description = ?, skills = ?, education = ?, salary_range = ?, job_outlook = ? WHERE id = ?");
                    $stmt->bind_param("ssssssi", $title, $description, $skills, $education, $salary_range, $job_outlook, $id);
                    
                    if ($stmt->execute()) {
                        $success_message = "Career profile updated successfully!";
                    } else {
                        $error_message = "Error updating career profile: " . $conn->error;
                    }
                } else {
                    $error_message = "Title and description are required fields.";
                }
                break;
                
            case 'delete':
                $id = $_POST['id'] ?? 0;
                
                if ($id > 0) {
                    $stmt = $conn->prepare("DELETE FROM career_profiles WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    
                    if ($stmt->execute()) {
                        $success_message = "Career profile deleted successfully!";
                    } else {
                        $error_message = "Error deleting career profile: " . $conn->error;
                    }
                }
                break;
        }
    }
}

// Get career for editing if ID is provided
$edit_career = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM career_profiles WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $edit_career = $result->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTech Admin - Career Profiles</title>
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
                <li><a href="careers.php" class="active"><i class="fas fa-briefcase"></i> Careers</a></li>
                <li><a href="stories.php"><i class="fas fa-book-open"></i> Inspiring Stories</a></li>
                <li><a href="tech_words.php"><i class="fas fa-language"></i> Tech Words</a></li>
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
            <h4>Career Profiles</h4>
            <div class="user-info">
                <span class="me-3"><?php echo htmlspecialchars($full_name); ?></span>
                <a href="logout.php" class="btn btn-sm btn-outline-danger">Logout</a>
            </div>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Career Profiles</h5>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCareerModal">
                            <i class="fas fa-plus-circle me-1"></i> Add New Career
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Search Bar -->
                        <form action="" method="GET" class="mb-4">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Search careers..." value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                                <?php if (!empty($search)): ?>
                                    <a href="careers.php" class="btn btn-outline-secondary">Clear</a>
                                <?php endif; ?>
                            </div>
                        </form>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Salary Range</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($careers)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No career profiles found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($careers as $career): ?>
                                            <tr>
                                                <td><?php echo $career['id']; ?></td>
                                                <td><?php echo htmlspecialchars($career['title']); ?></td>
                                                <td><?php echo htmlspecialchars(substr($career['description'], 0, 100)) . '...'; ?></td>
                                                <td><?php echo htmlspecialchars($career['salary_range']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($career['created_at'])); ?></td>
                                                <td class="action-buttons">
                                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editCareerModal<?php echo $career['id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteCareerModal<?php echo $career['id']; ?>">
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

    <!-- Add Career Modal -->
    <div class="modal fade" id="addCareerModal" tabindex="-1" aria-labelledby="addCareerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCareerModalLabel">Add New Career Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="skills" class="form-label">Skills</label>
                            <textarea class="form-control" id="skills" name="skills" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="education" class="form-label">Education</label>
                            <textarea class="form-control" id="education" name="education" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="salary_range" class="form-label">Salary Range</label>
                            <input type="text" class="form-control" id="salary_range" name="salary_range" required>
                        </div>
                        <div class="mb-3">
                            <label for="job_outlook" class="form-label">Job Outlook</label>
                            <textarea class="form-control" id="job_outlook" name="job_outlook" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Career</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Career Modals -->
    <?php foreach ($careers as $career): ?>
    <div class="modal fade" id="editCareerModal<?php echo $career['id']; ?>" tabindex="-1" aria-labelledby="editCareerModalLabel<?php echo $career['id']; ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCareerModalLabel<?php echo $career['id']; ?>">Edit Career Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" value="<?php echo $career['id']; ?>">
                        <div class="mb-3">
                            <label for="title<?php echo $career['id']; ?>" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title<?php echo $career['id']; ?>" name="title" value="<?php echo htmlspecialchars($career['title']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="description<?php echo $career['id']; ?>" class="form-label">Description</label>
                            <textarea class="form-control" id="description<?php echo $career['id']; ?>" name="description" rows="3" required><?php echo htmlspecialchars($career['description']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="skills<?php echo $career['id']; ?>" class="form-label">Skills</label>
                            <textarea class="form-control" id="skills<?php echo $career['id']; ?>" name="skills" rows="3" required><?php echo htmlspecialchars($career['skills']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="education<?php echo $career['id']; ?>" class="form-label">Education</label>
                            <textarea class="form-control" id="education<?php echo $career['id']; ?>" name="education" rows="3" required><?php echo htmlspecialchars($career['education']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="salary_range<?php echo $career['id']; ?>" class="form-label">Salary Range</label>
                            <input type="text" class="form-control" id="salary_range<?php echo $career['id']; ?>" name="salary_range" value="<?php echo htmlspecialchars($career['salary_range']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="job_outlook<?php echo $career['id']; ?>" class="form-label">Job Outlook</label>
                            <textarea class="form-control" id="job_outlook<?php echo $career['id']; ?>" name="job_outlook" rows="3" required><?php echo htmlspecialchars($career['job_outlook']); ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Career</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Delete Career Modals -->
    <?php foreach ($careers as $career): ?>
    <div class="modal fade" id="deleteCareerModal<?php echo $career['id']; ?>" tabindex="-1" aria-labelledby="deleteCareerModalLabel<?php echo $career['id']; ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteCareerModalLabel<?php echo $career['id']; ?>">Delete Career Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the career profile: <strong><?php echo htmlspecialchars($career['title']); ?></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $career['id']; ?>">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

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