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

// Handle search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$tech_words = [];

if (!empty($search)) {
    $search_term = "%$search%";
    $stmt = $conn->prepare("SELECT * FROM tech_words WHERE word LIKE ? OR definition LIKE ? OR example LIKE ? OR category LIKE ? ORDER BY created_at DESC");
    $stmt->bind_param("ssss", $search_term, $search_term, $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $tech_words[] = $row;
    }
} else {
    // Get all tech words
    $result = $conn->query("SELECT * FROM tech_words ORDER BY created_at DESC");
    while ($row = $result->fetch_assoc()) {
        $tech_words[] = $row;
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $word = $_POST['word'] ?? '';
                $definition = $_POST['definition'] ?? '';
                
                if (!empty($word) && !empty($definition)) {
                    // Start transaction
                    $conn->begin_transaction();
                    
                    try {
                        // Insert tech word
                        $stmt = $conn->prepare("INSERT INTO tech_words (word, definition) VALUES (?, ?)");
                        $stmt->bind_param("ss", $word, $definition);
                        $stmt->execute();
                        $word_id = $conn->insert_id;
                        
                        $conn->commit();
                        $success_message = "Tech word added successfully!";
                    } catch (Exception $e) {
                        $conn->rollback();
                        $error_message = "Error adding tech word: " . $e->getMessage();
                    }
                } else {
                    $error_message = "Word and definition are required fields.";
                }
                break;
                
            case 'edit':
                $id = $_POST['id'] ?? 0;
                $word = $_POST['word'] ?? '';
                $definition = $_POST['definition'] ?? '';
                
                if ($id > 0 && !empty($word) && !empty($definition)) {
                    // Start transaction
                    $conn->begin_transaction();
                    
                    try {
                        // Update tech word
                        $stmt = $conn->prepare("UPDATE tech_words SET word = ?, definition = ? WHERE id = ?");
                        $stmt->bind_param("ssi", $word, $definition, $id);
                        $stmt->execute();
                        
                        $conn->commit();
                        $success_message = "Tech word updated successfully!";
                    } catch (Exception $e) {
                        $conn->rollback();
                        $error_message = "Error updating tech word: " . $e->getMessage();
                    }
                } else {
                    $error_message = "Word and definition are required fields.";
                }
                break;
                
            case 'delete':
                $id = $_POST['id'] ?? 0;
                
                if ($id > 0) {
                    // Start transaction
                    $conn->begin_transaction();
                    
                    try {
                        // Delete tech word
                        $stmt = $conn->prepare("DELETE FROM tech_words WHERE id = ?");
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        
                        $conn->commit();
                        $success_message = "Tech word deleted successfully!";
                    } catch (Exception $e) {
                        $conn->rollback();
                        $error_message = "Error deleting tech word: " . $e->getMessage();
                    }
                }
                break;
        }
    }
}

// Get all tech words with their related careers
$tech_words = [];
$result = $conn->query("
    SELECT t.*, GROUP_CONCAT(c.title) as career_titles 
    FROM tech_words t 
    LEFT JOIN word_careers wc ON t.id = wc.word_id 
    LEFT JOIN career_profiles c ON wc.career_id = c.id 
    GROUP BY t.id 
    ORDER BY t.word ASC
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $tech_words[] = $row;
    }
} else {
    // If the query fails, get tech words without career relationships
    $result = $conn->query("SELECT * FROM tech_words ORDER BY word ASC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row['career_titles'] = '';
            $tech_words[] = $row;
        }
    }
}

// Get all careers for the dropdown
$careers = [];
$result = $conn->query("SELECT id, title FROM career_profiles ORDER BY title");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $careers[] = $row;
    }
}

// Get tech word for editing if ID is provided
$edit_word = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM tech_words WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $edit_word = $result->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTech Admin - Tech Words</title>
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
                <li><a href="admin_careers.php"><i class="fas fa-briefcase"></i> Careers</a></li>
                <li><a href="stories.php"><i class="fas fa-book-open"></i> Inspiring Stories</a></li>
                <li><a href="admin_tech_words.php" class="active"><i class="fas fa-language"></i> Tech Words</a></li>
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
            <h4>Tech Words</h4>
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
                        <h5 class="mb-0">Tech Words</h5>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addWordModal">
                            <i class="fas fa-plus-circle me-1"></i> Add New Tech Word
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Search Bar -->
                        <form action="" method="GET" class="mb-4">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Search tech words..." value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                                <?php if (!empty($search)): ?>
                                    <a href="tech_words.php" class="btn btn-outline-secondary">Clear</a>
                                <?php endif; ?>
                            </div>
                        </form>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Word</th>
                                        <th>Definition</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($tech_words)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No tech words found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($tech_words as $word): ?>
                                            <tr>
                                                <td><?php echo $word['id']; ?></td>
                                                <td><?php echo htmlspecialchars($word['word']); ?></td>
                                                <td><?php echo htmlspecialchars(substr($word['definition'], 0, 50)) . (strlen($word['definition']) > 50 ? '...' : ''); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($word['created_at'])); ?></td>
                                                <td class="action-buttons">
                                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editWordModal<?php echo $word['id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteWordModal<?php echo $word['id']; ?>">
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

    <!-- Add Word Modal -->
    <div class="modal fade" id="addWordModal" tabindex="-1" aria-labelledby="addWordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addWordModalLabel">Add New Tech Word</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label for="word" class="form-label">Word</label>
                            <input type="text" class="form-control" id="word" name="word" required>
                        </div>
                        <div class="mb-3">
                            <label for="definition" class="form-label">Definition</label>
                            <textarea class="form-control" id="definition" name="definition" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Word</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Word Modal -->
    <?php if ($edit_word): ?>
    <div class="modal fade" id="editWordModal<?php echo $edit_word['id']; ?>" tabindex="-1" aria-labelledby="editWordModalLabel<?php echo $edit_word['id']; ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editWordModalLabel<?php echo $edit_word['id']; ?>">Edit Tech Word</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" value="<?php echo $edit_word['id']; ?>">
                        <div class="mb-3">
                            <label for="word<?php echo $edit_word['id']; ?>" class="form-label">Word</label>
                            <input type="text" class="form-control" id="word<?php echo $edit_word['id']; ?>" name="word" value="<?php echo htmlspecialchars($edit_word['word']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="definition<?php echo $edit_word['id']; ?>" class="form-label">Definition</label>
                            <textarea class="form-control" id="definition<?php echo $edit_word['id']; ?>" name="definition" rows="3" required><?php echo htmlspecialchars($edit_word['definition']); ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Word</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Delete Word Modals -->
    <?php foreach ($tech_words as $word): ?>
    <div class="modal fade" id="deleteWordModal<?php echo $word['id']; ?>" tabindex="-1" aria-labelledby="deleteWordModalLabel<?php echo $word['id']; ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteWordModalLabel<?php echo $word['id']; ?>">Delete Tech Word</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the tech word: <strong><?php echo htmlspecialchars($word['word']); ?></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $word['id']; ?>">
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