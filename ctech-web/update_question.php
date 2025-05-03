<?php
session_start();
require_once 'config.php';

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

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['id']) || !isset($data['question']) || !isset($data['option1']) || 
    !isset($data['option2']) || !isset($data['option3']) || !isset($data['option4']) || 
    !isset($data['career_mappings'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$question_id = (int)$data['id'];
$question = $data['question'];
$option1 = $data['option1'];
$option2 = $data['option2'];
$option3 = $data['option3'];
$option4 = $data['option4'];
$career_mappings = $data['career_mappings'];

// Start transaction
$conn->begin_transaction();

try {
    // Update question
    $stmt = $conn->prepare("UPDATE quiz_questions SET question = ?, option1 = ?, option2 = ?, option3 = ?, option4 = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $question, $option1, $option2, $option3, $option4, $question_id);
    $stmt->execute();

    // Delete existing mappings
    $stmt = $conn->prepare("DELETE FROM quiz_results_mapping WHERE question_id = ?");
    $stmt->bind_param("i", $question_id);
    $stmt->execute();

    // Insert new mappings
    $stmt = $conn->prepare("INSERT INTO quiz_results_mapping (question_id, career_id, weight) VALUES (?, ?, ?)");
    
    foreach ($career_mappings as $career_id => $weight) {
        if ($weight > 0) { // Only insert if weight is greater than 0
            $stmt->bind_param("iii", $question_id, $career_id, $weight);
            $stmt->execute();
        }
    }

    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} 