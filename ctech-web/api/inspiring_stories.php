<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config.php';

// Make sure we have the database connection
if (!isset($conn)) {
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed'
    ]);
    exit;
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $result = $conn->query("SELECT * FROM inspiring_stories");
        $stories = [];
        while ($row = $result->fetch_assoc()) {
            $stories[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'role' => $row['role'],
                'company' => $row['company'],
                'image_path' => $row['image_path'] ?? null,
                'short_quote' => $row['short_quote'],
                'full_story' => $row['full_story'],
                'audio_path' => $row['audio_path'] ?? null,
                'related_careers' => json_decode($row['related_careers'] ?? '[]', true)
            ];
        }
        echo json_encode($stories);
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $requiredFields = ['name', 'role', 'company', 'short_quote', 'full_story'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "Missing required field: $field"]);
                exit;
            }
        }
        
        $stmt = $conn->prepare("INSERT INTO inspiring_stories (name, role, company, image_path, short_quote, full_story, audio_path, related_careers) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss",
            $data['name'],
            $data['role'],
            $data['company'],
            $data['image_path'] ?? null,
            $data['short_quote'],
            $data['full_story'],
            $data['audio_path'] ?? null,
            json_encode($data['related_careers'] ?? [])
        );
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to add story']);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing story ID']);
            exit;
        }
        
        $stmt = $conn->prepare("UPDATE inspiring_stories SET name = ?, role = ?, company = ?, image_path = ?, short_quote = ?, full_story = ?, audio_path = ?, related_careers = ? WHERE id = ?");
        $stmt->bind_param("ssssssssi",
            $data['name'],
            $data['role'],
            $data['company'],
            $data['image_path'] ?? null,
            $data['short_quote'],
            $data['full_story'],
            $data['audio_path'] ?? null,
            json_encode($data['related_careers'] ?? []),
            $data['id']
        );
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update story']);
        }
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing story ID']);
            exit;
        }
        
        $stmt = $conn->prepare("DELETE FROM inspiring_stories WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete story']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}