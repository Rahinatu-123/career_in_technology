<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        try {
            $stmt = $pdo->query("SELECT * FROM inspiring_stories");
            $stories = $stmt->fetchAll();
            echo json_encode($stories);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            ]);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        try {
            $stmt = $pdo->prepare("INSERT INTO inspiring_stories (name, role, company, image_path, short_quote, full_story, audio_path, related_careers) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['name'],
                $data['role'],
                $data['company'],
                $data['image_path'],
                $data['short_quote'],
                $data['full_story'],
                $data['audio_path'],
                json_encode($data['related_careers'])
            ]);
            echo json_encode([
                'success' => true,
                'id' => $pdo->lastInsertId()
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            ]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        try {
            $stmt = $pdo->prepare("UPDATE inspiring_stories SET name = ?, role = ?, company = ?, image_path = ?, short_quote = ?, full_story = ?, audio_path = ?, related_careers = ? WHERE id = ?");
            $stmt->execute([
                $data['name'],
                $data['role'],
                $data['company'],
                $data['image_path'],
                $data['short_quote'],
                $data['full_story'],
                $data['audio_path'],
                json_encode($data['related_careers']),
                $data['id']
            ]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            ]);
        }
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? null;
        if ($id) {
            try {
                $stmt = $pdo->prepare("DELETE FROM inspiring_stories WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true]);
            } catch (PDOException $e) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Database error: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'ID is required'
            ]);
        }
        break;

    default:
        echo json_encode([
            'success' => false,
            'error' => 'Invalid request method'
        ]);
} 