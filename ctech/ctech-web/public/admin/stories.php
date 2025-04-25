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
$stories = [];

if (!empty($search)) {
    $search_term = "%$search%";
    $stmt = $conn->prepare("SELECT * FROM inspiring_stories WHERE name LIKE ? OR role LIKE ? OR company LIKE ? OR short_quote LIKE ? OR full_story LIKE ? ORDER BY created_at DESC");
    $stmt->bind_param("sssss", $search_term, $search_term, $search_term, $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $stories[] = $row;
    }
} else {
    // Get all stories
    $result = $conn->query("SELECT * FROM inspiring_stories ORDER BY created_at DESC");
    while ($row = $result->fetch_assoc()) {
        $stories[] = $row;
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = $_POST['name'] ?? '';
                $role = $_POST['role'] ?? '';
                $company = $_POST['company'] ?? '';
                $story = $_POST['story'] ?? '';
                $career_ids = $_POST['career_ids'] ?? [];
                
                if (!empty($name) && !empty($story)) {
                    // Start transaction
                    $conn->begin_transaction();
                    
                    try {
                        // Insert story
                        $stmt = $conn->prepare("INSERT INTO inspiring_stories (name, role, company, story) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("ssss", $name, $role, $company, $story);
                        $stmt->execute();
                        $story_id = $conn->insert_id;
                        
                        // Insert career relationships
                        if (!empty($career_ids)) {
                            $stmt = $conn->prepare("INSERT INTO story_careers (story_id, career_id) VALUES (?, ?)");
                            foreach ($career_ids as $career_id) {
                                $stmt->bind_param("ii", $story_id, $career_id);
                                $stmt->execute();
                            }
                        }
                        
                        $conn->commit();
                        $success_message = "Story added successfully!";
                    } catch (Exception $e) {
                        $conn->rollback();
                        $error_message = "Error adding story: " . $e->getMessage();
                    }
                } else {
                    $error_message = "Name and story are required fields.";
                }
                break;
                
            case 'edit':
                $id = $_POST['id'] ?? 0;
                $name = $_POST['name'] ?? '';
                $role = $_POST['role'] ?? '';
                $company = $_POST['company'] ?? '';
                $story = $_POST['story'] ?? '';
                $career_ids = $_POST['career_ids'] ?? [];
                
                if ($id > 0 && !empty($name) && !empty($story)) {
                    // Start transaction
                    $conn->begin_transaction();
                    
                    try {
                        // Update story
                        $stmt = $conn->prepare("UPDATE inspiring_stories SET name = ?, role = ?, company = ?, story = ? WHERE id = ?");
                        $stmt->bind_param("ssssi", $name, $role, $company, $story, $id);
                        $stmt->execute();
                        
                        // Delete existing career relationships
                        $stmt = $conn->prepare("DELETE FROM story_careers WHERE story_id = ?");
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        
                        // Insert new career relationships
                        if (!empty($career_ids)) {
                            $stmt = $conn->prepare("INSERT INTO story_careers (story_id, career_id) VALUES (?, ?)");
                            foreach ($career_ids as $career_id) {
                                $stmt->bind_param("ii", $id, $career_id);
                                $stmt->execute();
                            }
                        }
                        
                        $conn->commit();
                        $success_message = "Story updated successfully!";
                    } catch (Exception $e) {
                        $conn->rollback();
                        $error_message = "Error updating story: " . $e->getMessage();
                    }
                } else {
                    $error_message = "Name and story are required fields.";
                }
                break;
                
            case 'delete':
                $id = $_POST['id'] ?? 0;
                
                if ($id > 0) {
                    // Start transaction
                    $conn->begin_transaction();
                    
                    try {
                        // Delete career relationships first
                        $stmt = $conn->prepare("DELETE FROM story_careers WHERE story_id = ?");
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        
                        // Delete story
                        $stmt = $conn->prepare("DELETE FROM inspiring_stories WHERE id = ?");
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        
                        $conn->commit();
                        $success_message = "Story deleted successfully!";
                    } catch (Exception $e) {
                        $conn->rollback();
                        $error_message = "Error deleting story: " . $e->getMessage();
                    }
                }
                break;
        }
    }
}

// Get all stories with their related careers
$stories = [];
$result = $conn->query("
    SELECT s.*, GROUP_CONCAT(c.title) as career_titles 
    FROM inspiring_stories s 
    LEFT JOIN story_careers sc ON s.id = sc.story_id 
    LEFT JOIN career_profiles c ON sc.career_id = c.id 
    GROUP BY s.id 
    ORDER BY s.created_at DESC
");
while ($row = $result->fetch_assoc()) {
    $stories[] = $row;
}

// Get all careers for the dropdown
$careers = [];
$result = $conn->query("SELECT id, title FROM career_profiles ORDER BY title");
while ($row = $result->fetch_assoc()) {
    $careers[] = $row;
}

// Get story for editing if ID is provided
$edit_story = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM inspiring_stories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $edit_story = $result->fetch_assoc();
        
        // Get related careers
        $stmt = $conn->prepare("SELECT career_id FROM story_careers WHERE story_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $edit_story['career_ids'] = [];
        while ($row = $result->fetch_assoc()) {
            $edit_story['career_ids'][] = $row['career_id'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTech Admin - Inspiring Stories</title>
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
                <li><a href="careers.php"><i class="fas fa-briefcase"></i> Careers</a></li>
                <li><a href="stories.php" class="active"><i class="fas fa-book-open"></i> Inspiring Stories</a></li>
                <li><a href="tech_words.php"><i class="fas fa-language"></i> Tech Words</a></li>
                <li><a href="quiz.php"><i class="fas fa-question-circle"></i> Career Quiz</a></li>
                <li><a href="app_users.php"><i class="fas fa-users"></i> App Users</a></li>
                <li><a href="analytics.php"><i class="fas fa-chart-bar"></i> Analytics</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <h4>Inspiring Stories</h4>
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
                        <h5 class="mb-0">Inspiring Stories</h5>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addStoryModal">
                            <i class="fas fa-plus-circle me-1"></i> Add New Story
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Search Bar -->
                        <form action="" method="GET" class="mb-4">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Search stories..." value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                                <?php if (!empty($search)): ?>
                                    <a href="stories.php" class="btn btn-outline-secondary">Clear</a>
                                <?php endif; ?>
                            </div>
                        </form>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Role</th>
                                        <th>Company</th>
                                        <th>Related Careers</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($stories)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No inspiring stories found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($stories as $story): ?>
                                            <tr>
                                                <td><?php echo $story['id']; ?></td>
                                                <td><?php echo htmlspecialchars($story['name']); ?></td>
                                                <td><?php echo htmlspecialchars($story['role']); ?></td>
                                                <td><?php echo htmlspecialchars($story['company']); ?></td>
                                                <td><?php echo htmlspecialchars($story['career_titles'] ?? 'None'); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($story['created_at'])); ?></td>
                                                <td class="action-buttons">
                                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editStoryModal<?php echo $story['id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteStoryModal<?php echo $story['id']; ?>">
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

    <!-- Add Story Modal -->
    <div class="modal fade" id="addStoryModal" tabindex="-1" aria-labelledby="addStoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStoryModalLabel">Add New Story</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <input type="text" class="form-control" id="role" name="role" required>
                        </div>
                        <div class="mb-3">
                            <label for="company" class="form-label">Company</label>
                            <input type="text" class="form-control" id="company" name="company" required>
                        </div>
                        <div class="mb-3">
                            <label for="story" class="form-label">Story</label>
                            <textarea class="form-control" id="story" name="story" rows="5" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="career_ids" class="form-label">Related Careers</label>
                            <select class="form-select" id="career_ids" name="career_ids[]" multiple>
                                <?php foreach ($careers as $career): ?>
                                    <option value="<?php echo $career['id']; ?>"><?php echo htmlspecialchars($career['title']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Hold Ctrl/Cmd to select multiple careers</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Story</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Story Modals -->
    <?php foreach ($stories as $story): ?>
    <div class="modal fade" id="editStoryModal<?php echo $story['id']; ?>" tabindex="-1" aria-labelledby="editStoryModalLabel<?php echo $story['id']; ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editStoryModalLabel<?php echo $story['id']; ?>">Edit Story</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" value="<?php echo $story['id']; ?>">
                        <div class="mb-3">
                            <label for="name<?php echo $story['id']; ?>" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name<?php echo $story['id']; ?>" name="name" value="<?php echo htmlspecialchars($story['name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="role<?php echo $story['id']; ?>" class="form-label">Role</label>
                            <input type="text" class="form-control" id="role<?php echo $story['id']; ?>" name="role" value="<?php echo htmlspecialchars($story['role']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="company<?php echo $story['id']; ?>" class="form-label">Company</label>
                            <input type="text" class="form-control" id="company<?php echo $story['id']; ?>" name="company" value="<?php echo htmlspecialchars($story['company']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="story<?php echo $story['id']; ?>" class="form-label">Story</label>
                            <textarea class="form-control" id="story<?php echo $story['id']; ?>" name="story" rows="5" required><?php echo htmlspecialchars($story['story']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="career_ids<?php echo $story['id']; ?>" class="form-label">Related Careers</label>
                            <select class="form-select" id="career_ids<?php echo $story['id']; ?>" name="career_ids[]" multiple>
                                <?php 
                                $story_careers = explode(',', $story['career_titles'] ?? '');
                                foreach ($careers as $career): 
                                    $selected = in_array($career['title'], $story_careers) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $career['id']; ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($career['title']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Hold Ctrl/Cmd to select multiple careers</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Story</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Delete Story Modals -->
    <?php foreach ($stories as $story): ?>
    <div class="modal fade" id="deleteStoryModal<?php echo $story['id']; ?>" tabindex="-1" aria-labelledby="deleteStoryModalLabel<?php echo $story['id']; ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteStoryModalLabel<?php echo $story['id']; ?>">Delete Story</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the story: <strong><?php echo htmlspecialchars($story['name']); ?></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $story['id']; ?>">
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