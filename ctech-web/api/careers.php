<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        try {
            $search = $_GET['search'] ?? '';
            $minSalary = $_GET['min_salary'] ?? null;
            $educationLevel = $_GET['education_level'] ?? null;

            $query = "SELECT * FROM career_profiles WHERE 1=1";
            $params = [];

            if ($search) {
                $query .= " AND (title LIKE ? OR description LIKE ?)";
                $searchTerm = "%$search%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            if ($minSalary) {
                $query .= " AND salary_range_min >= ?";
                $params[] = $minSalary;
            }

            if ($educationLevel) {
                $query .= " AND education_level = ?";
                $params[] = $educationLevel;
            }

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $careers = $stmt->fetchAll();
            echo json_encode($careers);
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
            $stmt = $pdo->prepare("INSERT INTO career_profiles (title, description, skills, education, salary_range, job_outlook, image_path, video_path, audio_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['title'],
                $data['description'],
                $data['skills'],
                $data['education'],
                $data['salary_range'],
                $data['job_outlook'],
                $data['image_path'],
                $data['video_path'],
                $data['audio_path']
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
            $stmt = $pdo->prepare("UPDATE career_profiles SET title = ?, description = ?, skills = ?, education = ?, salary_range = ?, job_outlook = ?, image_path = ?, video_path = ?, audio_path = ? WHERE id = ?");
            $stmt->execute([
                $data['title'],
                $data['description'],
                $data['skills'],
                $data['education'],
                $data['salary_range'],
                $data['job_outlook'],
                $data['image_path'],
                $data['video_path'],
                $data['audio_path'],
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
                $stmt = $pdo->prepare("DELETE FROM career_profiles WHERE id = ?");
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