<?php
header('Content-Type: application/json');
require_once '../config.php';

// Handle CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Check if table exists
$result = $conn->query("SHOW TABLES LIKE 'quiz_questions'");
if ($result->num_rows == 0) {
    http_response_code(500);
    echo json_encode(['error' => 'Quiz questions table not found']);
    exit;
}

// Get all quiz questions
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT id, question, option_a, option_b, option_c, option_d, correct_option FROM quiz_questions ORDER BY id";
    $result = $conn->query($query);
    
    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => $conn->error]);
        exit;
    }
    
    $questions = [];
    while ($row = $result->fetch_assoc()) {
        $questions[] = [
            'id' => $row['id'],
            'question' => $row['question'],
            'options' => [
                ['text' => $row['option_a'], 'is_correct' => $row['correct_option'] === 'a'],
                ['text' => $row['option_b'], 'is_correct' => $row['correct_option'] === 'b'],
                ['text' => $row['option_c'], 'is_correct' => $row['correct_option'] === 'c'],
                ['text' => $row['option_d'], 'is_correct' => $row['correct_option'] === 'd']
            ]
        ];
    }
    
    echo json_encode($questions);
    exit;
}

// Add new quiz question
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $requiredFields = ['question', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_option'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing required field: $field"]);
            exit;
        }
    }
    
    // Validate correct_option
    if (!in_array($data['correct_option'], ['a', 'b', 'c', 'd'])) {
        http_response_code(400);
        echo json_encode(['error' => 'correct_option must be a, b, c, or d']);
        exit;
    }
    
    $stmt = $conn->prepare("INSERT INTO quiz_questions (question, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", 
        $data['question'],
        $data['option_a'],
        $data['option_b'],
        $data['option_c'],
        $data['option_d'],
        $data['correct_option']
    );
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to add quiz question: ' . $conn->error]);
    }
    exit;
}

// Update quiz question
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing question ID']);
        exit;
    }
    
    $requiredFields = ['question', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_option'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing required field: $field"]);
            exit;
        }
    }
    
    // Validate correct_option
    if (!in_array($data['correct_option'], ['a', 'b', 'c', 'd'])) {
        http_response_code(400);
        echo json_encode(['error' => 'correct_option must be a, b, c, or d']);
        exit;
    }
    
    $stmt = $conn->prepare("UPDATE quiz_questions SET question = ?, option_a = ?, option_b = ?, option_c = ?, option_d = ?, correct_option = ? WHERE id = ?");
    $stmt->bind_param("ssssssi", 
        $data['question'],
        $data['option_a'],
        $data['option_b'],
        $data['option_c'],
        $data['option_d'],
        $data['correct_option'],
        $data['id']
    );
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update quiz question: ' . $conn->error]);
    }
    exit;
}

// Delete quiz question
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing question ID']);
        exit;
    }
    
    $stmt = $conn->prepare("DELETE FROM quiz_questions WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete quiz question: ' . $conn->error]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);