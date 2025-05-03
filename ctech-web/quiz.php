<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if user has appropriate role
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'editor') {
    header("Location: index.php");
    exit;
}

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_question') {
            $question = $_POST['question'] ?? '';
            $option_a = $_POST['option_a'] ?? '';
            $option_b = $_POST['option_b'] ?? '';
            $option_c = $_POST['option_c'] ?? '';
            $option_d = $_POST['option_d'] ?? '';
            $correct_option = $_POST['correct_option'] ?? '';
            
            if (empty($question) || empty($option_a) || empty($option_b) || empty($option_c) || empty($option_d) || empty($correct_option)) {
                $error_message = 'All fields are required';
            } else {
                $stmt = $conn->prepare("INSERT INTO quiz_questions (question, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $question, $option_a, $option_b, $option_c, $option_d, $correct_option);
                
                if ($stmt->execute()) {
                    $question_id = $stmt->insert_id;
                    
                    // Add career mappings if selected
                    if (isset($_POST['careers']) && is_array($_POST['careers'])) {
                        foreach ($_POST['careers'] as $career_id) {
                            $weight = $_POST['weight_' . $career_id] ?? 1;
                            $mapping_stmt = $conn->prepare("INSERT INTO quiz_results_mapping (question_id, career_id, weight) VALUES (?, ?, ?)");
                            $mapping_stmt->bind_param("iii", $question_id, $career_id, $weight);
                            $mapping_stmt->execute();
                        }
                    }
                    
                    $success_message = 'Question added successfully';
                } else {
                    $error_message = 'Error adding question: ' . $conn->error;
                }
            }
        } elseif ($_POST['action'] === 'delete_question' && isset($_POST['question_id'])) {
            $question_id = $_POST['question_id'];
            
            $stmt = $conn->prepare("DELETE FROM quiz_questions WHERE id = ?");
            $stmt->bind_param("i", $question_id);
            
            if ($stmt->execute()) {
                $success_message = 'Question deleted successfully';
            } else {
                $error_message = 'Error deleting question: ' . $conn->error;
            }
        } elseif ($_POST['action'] === 'update_question' && isset($_POST['question_id'])) {
            $question_id = $_POST['question_id'];
            $question = $_POST['question'] ?? '';
            $option_a = $_POST['option_a'] ?? '';
            $option_b = $_POST['option_b'] ?? '';
            $option_c = $_POST['option_c'] ?? '';
            $option_d = $_POST['option_d'] ?? '';
            $correct_option = $_POST['correct_option'] ?? '';
            
            if (empty($question) || empty($option_a) || empty($option_b) || empty($option_c) || empty($option_d) || empty($correct_option)) {
                $error_message = 'All fields are required';
            } else {
                $stmt = $conn->prepare("UPDATE quiz_questions SET question = ?, option_a = ?, option_b = ?, option_c = ?, option_d = ?, correct_option = ? WHERE id = ?");
                $stmt->bind_param("ssssssi", $question, $option_a, $option_b, $option_c, $option_d, $correct_option, $question_id);
                
                if ($stmt->execute()) {
                    // Update career mappings
                    if (isset($_POST['careers']) && is_array($_POST['careers'])) {
                        // First delete existing mappings
                        $delete_stmt = $conn->prepare("DELETE FROM quiz_results_mapping WHERE question_id = ?");
                        $delete_stmt->bind_param("i", $question_id);
                        $delete_stmt->execute();
                        
                        // Then add new mappings
                        foreach ($_POST['careers'] as $career_id) {
                            $weight = $_POST['weight_' . $career_id] ?? 1;
                            $mapping_stmt = $conn->prepare("INSERT INTO quiz_results_mapping (question_id, career_id, weight) VALUES (?, ?, ?)");
                            $mapping_stmt->bind_param("iii", $question_id, $career_id, $weight);
                            $mapping_stmt->execute();
                        }
                    }
                    
                    $success_message = 'Question updated successfully';
                } else {
                    $error_message = 'Error updating question: ' . $conn->error;
                }
            }
        }
    }
}

// Get all quiz questions
$questions = [];
$result = $conn->query("SELECT * FROM quiz_questions ORDER BY id DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }
}

// Get all careers for mapping
$careers = [];
$result = $conn->query("SELECT id, title FROM career_profiles ORDER BY title");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $careers[] = $row;
    }
}

// Get career mappings for each question
$question_careers = [];
foreach ($questions as $question) {
    $question_careers[$question['id']] = [];
    $result = $conn->query("SELECT career_id, weight FROM quiz_results_mapping WHERE question_id = " . $question['id']);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $question_careers[$question['id']][$row['career_id']] = $row['weight'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Career Quiz - CTech Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
        }
        .sidebar a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
        }
        .sidebar a:hover {
            color: white;
        }
        .content {
            padding: 20px;
        }
        .nav-link.active {
            background-color: #495057;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="p-3">
                    <h3 class="text-center">CTech Admin</h3>
                    <hr>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_careers.php">
                                <i class="fas fa-briefcase me-2"></i> Careers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="stories.php">
                                <i class="fas fa-book me-2"></i> Stories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_tech_words.php">
                                <i class="fas fa-code me-2"></i> Tech Words
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="quiz.php">
                                <i class="fas fa-question-circle me-2"></i> Career Quiz
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="app_users.php">
                                <i class="fas fa-users me-2"></i> App Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="analytics.php">
                                <i class="fas fa-chart-bar me-2"></i> Analytics
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 content">
                <h2 class="mb-4">Career Quiz Management</h2>
                
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <!-- Add Question Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Add New Question</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="add_question">
                            
                            <div class="mb-3">
                                <label for="question" class="form-label">Question</label>
                                <textarea class="form-control" id="question" name="question" rows="3" required></textarea>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="option_a" class="form-label">Option A</label>
                                    <input type="text" class="form-control" id="option_a" name="option_a" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="option_b" class="form-label">Option B</label>
                                    <input type="text" class="form-control" id="option_b" name="option_b" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="option_c" class="form-label">Option C</label>
                                    <input type="text" class="form-control" id="option_c" name="option_c" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="option_d" class="form-label">Option D</label>
                                    <input type="text" class="form-control" id="option_d" name="option_d" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="correct_option" class="form-label">Correct Option</label>
                                <select class="form-select" id="correct_option" name="correct_option" required>
                                    <option value="">Select correct option</option>
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="C">C</option>
                                    <option value="D">D</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Career Mappings</label>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Career</th>
                                                <th>Weight (1-5)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($careers as $career): ?>
                                                <tr>
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input career-checkbox" type="checkbox" name="careers[]" value="<?php echo $career['id']; ?>" id="career_<?php echo $career['id']; ?>">
                                                            <label class="form-check-label" for="career_<?php echo $career['id']; ?>">
                                                                <?php echo htmlspecialchars($career['title']); ?>
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control weight-input" name="weight_<?php echo $career['id']; ?>" value="1" min="1" max="5" disabled>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Add Question</button>
                        </form>
                    </div>
                </div>
                
                <!-- Questions List -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Quiz Questions</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($questions)): ?>
                            <p>No questions found. Add your first question above.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Question</th>
                                            <th>Options</th>
                                            <th>Correct</th>
                                            <th>Career Mappings</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($questions as $question): ?>
                                            <tr>
                                                <td><?php echo $question['id']; ?></td>
                                                <td><?php echo htmlspecialchars($question['question']); ?></td>
                                                <td>
                                                    A: <?php echo htmlspecialchars($question['option_a']); ?><br>
                                                    B: <?php echo htmlspecialchars($question['option_b']); ?><br>
                                                    C: <?php echo htmlspecialchars($question['option_c']); ?><br>
                                                    D: <?php echo htmlspecialchars($question['option_d']); ?>
                                                </td>
                                                <td><?php echo $question['correct_option']; ?></td>
                                                <td>
                                                    <?php 
                                                    if (isset($question_careers[$question['id']]) && !empty($question_careers[$question['id']])) {
                                                        foreach ($question_careers[$question['id']] as $career_id => $weight) {
                                                            foreach ($careers as $career) {
                                                                if ($career['id'] == $career_id) {
                                                                    echo htmlspecialchars($career['title']) . ' (Weight: ' . $weight . ')<br>';
                                                                    break;
                                                                }
                                                            }
                                                        }
                                                    } else {
                                                        echo 'No career mappings';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-primary edit-question" data-id="<?php echo $question['id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form method="POST" action="" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this question?');">
                                                        <input type="hidden" name="action" value="delete_question">
                                                        <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Question Modal -->
    <div class="modal fade" id="editQuestionModal" tabindex="-1" aria-labelledby="editQuestionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editQuestionModalLabel">Edit Question</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editQuestionForm" method="POST" action="">
                        <input type="hidden" name="action" value="update_question">
                        <input type="hidden" name="question_id" id="edit_question_id">
                        
                        <div class="mb-3">
                            <label for="edit_question" class="form-label">Question</label>
                            <textarea class="form-control" id="edit_question" name="question" rows="3" required></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_option_a" class="form-label">Option A</label>
                                <input type="text" class="form-control" id="edit_option_a" name="option_a" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_option_b" class="form-label">Option B</label>
                                <input type="text" class="form-control" id="edit_option_b" name="option_b" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_option_c" class="form-label">Option C</label>
                                <input type="text" class="form-control" id="edit_option_c" name="option_c" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_option_d" class="form-label">Option D</label>
                                <input type="text" class="form-control" id="edit_option_d" name="option_d" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_correct_option" class="form-label">Correct Option</label>
                            <select class="form-select" id="edit_correct_option" name="correct_option" required>
                                <option value="">Select correct option</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Career Mappings</label>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Career</th>
                                            <th>Weight (1-5)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="edit_career_mappings">
                                        <!-- Will be populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="editQuestionForm" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Enable/disable weight inputs based on checkbox selection
        document.querySelectorAll('.career-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const weightInput = document.querySelector(`input[name="weight_${this.value}"]`);
                weightInput.disabled = !this.checked;
            });
        });
        
        // Edit question functionality
        document.querySelectorAll('.edit-question').forEach(button => {
            button.addEventListener('click', function() {
                const questionId = this.getAttribute('data-id');
                
                // Fetch question data via AJAX
                fetch(`get_question.php?id=${questionId}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('edit_question_id').value = data.id;
                        document.getElementById('edit_question').value = data.question;
                        document.getElementById('edit_option_a').value = data.option_a;
                        document.getElementById('edit_option_b').value = data.option_b;
                        document.getElementById('edit_option_c').value = data.option_c;
                        document.getElementById('edit_option_d').value = data.option_d;
                        document.getElementById('edit_correct_option').value = data.correct_option;
                        
                        // Populate career mappings
                        const mappingsContainer = document.getElementById('edit_career_mappings');
                        mappingsContainer.innerHTML = '';
                        
                        // Get all careers
                        const careers = <?php echo json_encode($careers); ?>;
                        const questionCareers = <?php echo json_encode($question_careers); ?>;
                        
                        careers.forEach(career => {
                            const isChecked = questionCareers[questionId] && questionCareers[questionId][career.id] ? 'checked' : '';
                            const weight = isChecked ? questionCareers[questionId][career.id] : 1;
                            
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input edit-career-checkbox" type="checkbox" name="careers[]" value="${career.id}" id="edit_career_${career.id}" ${isChecked}>
                                        <label class="form-check-label" for="edit_career_${career.id}">
                                            ${career.title}
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" class="form-control edit-weight-input" name="weight_${career.id}" value="${weight}" min="1" max="5" ${!isChecked ? 'disabled' : ''}>
                                </td>
                            `;
                            mappingsContainer.appendChild(row);
                        });
                        
                        // Add event listeners to the new checkboxes
                        document.querySelectorAll('.edit-career-checkbox').forEach(checkbox => {
                            checkbox.addEventListener('change', function() {
                                const weightInput = document.querySelector(`#editQuestionModal input[name="weight_${this.value}"]`);
                                weightInput.disabled = !this.checked;
                            });
                        });
                        
                        // Show the modal
                        const modal = new bootstrap.Modal(document.getElementById('editQuestionModal'));
                        modal.show();
                    })
                    .catch(error => {
                        console.error('Error fetching question data:', error);
                        alert('Error loading question data. Please try again.');
                    });
            });
        });
    </script>
</body>
</html> 