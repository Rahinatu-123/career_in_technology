<?php
session_start();
require_once '../../db/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Check if user has appropriate role
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'editor') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

// Check if question ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid question ID']);
    exit;
}

$question_id = (int)$_GET['id'];

// Get question data
$stmt = $conn->prepare("SELECT * FROM quiz_questions WHERE id = ?");
$stmt->bind_param("i", $question_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Question not found']);
    exit;
}

$question = $result->fetch_assoc();

// Get career mappings for this question
$career_mappings = [];
$mapping_stmt = $conn->prepare("SELECT career_id, weight FROM quiz_results_mapping WHERE question_id = ?");
$mapping_stmt->bind_param("i", $question_id);
$mapping_stmt->execute();
$mapping_result = $mapping_stmt->get_result();

while ($row = $mapping_result->fetch_assoc()) {
    $career_mappings[$row['career_id']] = $row['weight'];
}

// Add career mappings to the response
$question['career_mappings'] = $career_mappings;

// Return the question data as JSON
header('Content-Type: application/json');
echo json_encode($question); 