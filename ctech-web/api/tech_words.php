<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../config.php';

// Database connection check
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// Table existence check
$tableCheck = $conn->query("SHOW TABLES LIKE 'tech_words'");
if ($tableCheck->num_rows == 0) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Tech words table not found']);
    exit;
}

// Helper function to sanitize input
function sanitizeInput($data) {
    return trim(htmlspecialchars(strip_tags($data)));
}

// Main request handler
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Get all tech words or filter by category
        $category = isset($_GET['category']) ? sanitizeInput($_GET['category']) : null;
        
        if ($category) {
            $stmt = $conn->prepare("SELECT id, word, definition, category, created_at 
                                  FROM tech_words 
                                  WHERE category = ? 
                                  ORDER BY word ASC");
            $stmt->bind_param("s", $category);
        } else {
            $stmt = $conn->prepare("SELECT id, word, definition, category, created_at 
                                  FROM tech_words 
                                  ORDER BY word ASC");
        }
        
        if (!$stmt->execute()) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to fetch tech words']);
            exit;
        }
        
        $result = $stmt->get_result();
        $words = [];
        while ($row = $result->fetch_assoc()) {
            $words[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $words]);
        break;

    case 'POST':
        // Create new tech word
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (empty($data['word']) || empty($data['definition'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Word and definition are required']);
            exit;
        }
        
        // Sanitize inputs
        $word = sanitizeInput($data['word']);
        $definition = sanitizeInput($data['definition']);
        $category = isset($data['category']) ? sanitizeInput($data['category']) : null;
        
        // Validate lengths
        if (strlen($word) > 100) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Word must be 100 characters or less']);
            exit;
        }
        
        // Check for duplicate word
        $check = $conn->prepare("SELECT id FROM tech_words WHERE LOWER(word) = LOWER(?)");
        $check->bind_param("s", $word);
        $check->execute();
        
        if ($check->get_result()->num_rows > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'Tech word already exists']);
            exit;
        }
        
        // Insert new record
        $stmt = $conn->prepare("INSERT INTO tech_words (word, definition, category) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $word, $definition, $category);
        
        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'id' => $stmt->insert_id,
                'word' => $word,
                'message' => 'Tech word added successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to add tech word']);
        }
        break;

    case 'PUT':
        // Update existing tech word
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Word ID is required']);
            exit;
        }
        
        // Sanitize inputs
        $id = (int)$data['id'];
        $word = isset($data['word']) ? sanitizeInput($data['word']) : null;
        $definition = isset($data['definition']) ? sanitizeInput($data['definition']) : null;
        $category = isset($data['category']) ? sanitizeInput($data['category']) : null;
        
        // Build dynamic update query
        $updates = [];
        $params = [];
        $types = '';
        
        if ($word !== null) {
            if (strlen($word) > 100) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Word must be 100 characters or less']);
                exit;
            }
            $updates[] = "word = ?";
            $params[] = $word;
            $types .= 's';
        }
        
        if ($definition !== null) {
            $updates[] = "definition = ?";
            $params[] = $definition;
            $types .= 's';
        }
        
        if ($category !== null) {
            $updates[] = "category = ?";
            $params[] = $category;
            $types .= 's';
        }
        
        if (empty($updates)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No fields to update']);
            exit;
        }
        
        $types .= 'i'; // for ID
        $params[] = $id;
        
        $query = "UPDATE tech_words SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Tech word updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to update tech word']);
        }
        break;

    case 'DELETE':
        // Delete tech word
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        
        if (empty($id)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Word ID is required']);
            exit;
        }
        
        $stmt = $conn->prepare("DELETE FROM tech_words WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Tech word deleted successfully']);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Tech word not found']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to delete tech word']);
        }
        break;

    case 'OPTIONS':
        // Preflight request
        http_response_code(200);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}

$conn->close();