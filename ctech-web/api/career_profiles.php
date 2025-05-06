<?php
header('Content-Type: application/json');
require_once '../config.php';

// Handle CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Get all career profiles
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $search = $_GET['search'] ?? '';
    $minSalary = $_GET['min_salary'] ?? null;
    $educationLevel = $_GET['education_level'] ?? null;
    
    $query = "SELECT * FROM career_profiles WHERE 1=1";
    $params = [];
    $types = "";
    
    if ($search) {
        $query .= " AND (title LIKE ? OR description LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "ss";
    }
    
    if ($minSalary) {
        $query .= " AND salary_range >= ?";
        $params[] = $minSalary;
        $types .= "d";
    }
    
    if ($educationLevel) {
        $query .= " AND education = ?";
        $params[] = $educationLevel;
        $types .= "s";
    }
    
    $query .= " ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $careers = [];
    while ($row = $result->fetch_assoc()) {
        $careers[] = [
            'id' => $row['id'] ?? null,
            'title' => $row['title'] ?? null,
            'description' => $row['description'] ?? null,
            'skills' => $row['skills'] ?? null,
            'education' => $row['education'] ?? null,
            'salary_range' => $row['salary_range'] ?? null,
            'job_outlook' => $row['job_outlook'] ?? null,
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
            'image_path' => $row['image_path'] ?? null,
            'video_path' => $row['video_path'] ?? null,
            'audio_path' => $row['audio_path'] ?? null
        ];
    }
    
    echo json_encode($careers);
    exit;
}

// Add new career profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $requiredFields = ['title', 'description', 'skills', 'education', 'salary_range', 'job_outlook'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing required field: $field"]);
            exit;
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO career_profiles (title, description, skills, education, salary_range, job_outlook, image_path, video_path, audio_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssdssss", 
        $data['title'],
        $data['description'],
        $data['skills'],
        $data['education'],
        $data['salary_range'],
        $data['job_outlook'],
        $data['image_path'] ?? null,
        $data['video_path'] ?? null,
        $data['audio_path'] ?? null
    );
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to add career profile']);
    }
    exit;
}

// Update career profile
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing career profile ID']);
        exit;
    }
    
    $stmt = $conn->prepare("UPDATE career_profiles SET title = ?, description = ?, skills = ?, education = ?, salary_range = ?, job_outlook = ?, image_path = ?, video_path = ?, audio_path = ? WHERE id = ?");
    $stmt->bind_param("ssssdssssi", 
        $data['title'],
        $data['description'],
        $data['skills'],
        $data['education'],
        $data['salary_range'],
        $data['job_outlook'],
        $data['image_path'] ?? null,
        $data['video_path'] ?? null,
        $data['audio_path'] ?? null,
        $data['id']
    );
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update career profile']);
    }
    exit;
}

// Delete career profile
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing career profile ID']);
        exit;
    }
    
    $stmt = $conn->prepare("DELETE FROM career_profiles WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete career profile']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);