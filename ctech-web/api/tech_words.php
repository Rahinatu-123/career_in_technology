<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        try {
            $stmt = $pdo->query("SELECT * FROM tech_words");
            $words = $stmt->fetchAll();
            echo json_encode($words);
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
            $stmt = $pdo->prepare("INSERT INTO tech_words (word, definition, category) VALUES (?, ?, ?)");
            $stmt->execute([$data['word'], $data['definition'], $data['category']]);
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
            $stmt = $pdo->prepare("UPDATE tech_words SET word = ?, definition = ?, category = ? WHERE id = ?");
            $stmt->execute([$data['word'], $data['definition'], $data['category'], $data['id']]);
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
                $stmt = $pdo->prepare("DELETE FROM tech_words WHERE id = ?");
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