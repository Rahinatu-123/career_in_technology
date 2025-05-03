<?php
header('Content-Type: application/json');
require_once '../config.php';

// Handle CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Get all quiz questions
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT * FROM quiz_questions ORDER BY id";
    $result = $conn->query($query);
    
    $questions = [];
    while ($row = $result->fetch_assoc()) {
        // Get options
        $optionsStmt = $conn->prepare("SELECT * FROM quiz_options WHERE question_id = ? ORDER BY id");
        $optionsStmt->bind_param("i", $row['id']);
        $optionsStmt->execute();
        $optionsResult = $optionsStmt->get_result();
        
        $options = [];
        while ($option = $optionsResult->fetch_assoc()) {
            $options[] = [
                'id' => $option['id'],
                'text' => $option['text'],
                'is_correct' => (bool)$option['is_correct']
            ];
        }
        
        $questions[] = [
            'id' => $row['id'],
            'question' => $row['question'],
            'options' => $options
        ];
    }
    
    echo json_encode($questions);
    exit;
}

// Add new quiz question
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['question']) || !isset($data['options']) || !is_array($data['options'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields: question and options']);
        exit;
    }
    
    // Validate options
    $hasCorrectOption = false;
    foreach ($data['options'] as $option) {
        if (!isset($option['text'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Each option must have a text field']);
            exit;
        }
        if (isset($option['is_correct']) && $option['is_correct']) {
            $hasCorrectOption = true;
        }
    }
    
    if (!$hasCorrectOption) {
        http_response_code(400);
        echo json_encode(['error' => 'At least one option must be marked as correct']);
        exit;
    }
    
    // Insert question
    $stmt = $conn->prepare("INSERT INTO quiz_questions (question) VALUES (?)");
    $stmt->bind_param("s", $data['question']);
    
    if ($stmt->execute()) {
        $questionId = $stmt->insert_id;
        
        // Insert options
        $optionStmt = $conn->prepare("INSERT INTO quiz_options (question_id, text, is_correct) VALUES (?, ?, ?)");
        foreach ($data['options'] as $option) {
            $isCorrect = isset($option['is_correct']) ? (int)$option['is_correct'] : 0;
            $optionStmt->bind_param("isi", $questionId, $option['text'], $isCorrect);
            $optionStmt->execute();
        }
        
        echo json_encode(['success' => true, 'id' => $questionId]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to add quiz question']);
    }
    exit;
}

// Update quiz question
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id']) || !isset($data['question']) || !isset($data['options']) || !is_array($data['options'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields: id, question, and options']);
        exit;
    }
    
    // Validate options
    $hasCorrectOption = false;
    foreach ($data['options'] as $option) {
        if (!isset($option['text']) || !isset($option['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Each option must have id and text fields']);
            exit;
        }
        if (isset($option['is_correct']) && $option['is_correct']) {
            $hasCorrectOption = true;
        }
    }
    
    if (!$hasCorrectOption) {
        http_response_code(400);
        echo json_encode(['error' => 'At least one option must be marked as correct']);
        exit;
    }
    
    // Update question
    $stmt = $conn->prepare("UPDATE quiz_questions SET question = ? WHERE id = ?");
    $stmt->bind_param("si", $data['question'], $data['id']);
    
    if ($stmt->execute()) {
        // Update options
        $optionStmt = $conn->prepare("UPDATE quiz_options SET text = ?, is_correct = ? WHERE id = ? AND question_id = ?");
        foreach ($data['options'] as $option) {
            $isCorrect = isset($option['is_correct']) ? (int)$option['is_correct'] : 0;
            $optionStmt->bind_param("siii", $option['text'], $isCorrect, $option['id'], $data['id']);
            $optionStmt->execute();
        }
        
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update quiz question']);
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
    
    // Delete options first
    $deleteOptionsStmt = $conn->prepare("DELETE FROM quiz_options WHERE question_id = ?");
    $deleteOptionsStmt->bind_param("i", $id);
    $deleteOptionsStmt->execute();
    
    // Delete the question
    $stmt = $conn->prepare("DELETE FROM quiz_questions WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete quiz question']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']); 