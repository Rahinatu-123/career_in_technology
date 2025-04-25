<?php
require_once 'config.php';

// Get all career profiles
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $conn = getDBConnection();
    
    $sql = "SELECT * FROM career_profiles";
    $result = $conn->query($sql);
    
    $profiles = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $profiles[] = $row;
        }
    }
    
    sendResponse($profiles);
}

// Add a new career profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['title']) || !isset($data['description'])) {
        sendResponse(['error' => 'Missing required fields'], 400);
    }
    
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("INSERT INTO career_profiles (title, description, skills, education, salary_range, job_outlook) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", 
        $data['title'],
        $data['description'],
        $data['skills'],
        $data['education'],
        $data['salary_range'],
        $data['job_outlook']
    );
    
    if ($stmt->execute()) {
        sendResponse(['message' => 'Career profile created successfully', 'id' => $stmt->insert_id]);
    } else {
        sendResponse(['error' => 'Failed to create career profile'], 500);
    }
}

// Update a career profile
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        sendResponse(['error' => 'Missing profile ID'], 400);
    }
    
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("UPDATE career_profiles SET title=?, description=?, skills=?, education=?, salary_range=?, job_outlook=? WHERE id=?");
    $stmt->bind_param("ssssssi", 
        $data['title'],
        $data['description'],
        $data['skills'],
        $data['education'],
        $data['salary_range'],
        $data['job_outlook'],
        $data['id']
    );
    
    if ($stmt->execute()) {
        sendResponse(['message' => 'Career profile updated successfully']);
    } else {
        sendResponse(['error' => 'Failed to update career profile'], 500);
    }
}

// Delete a career profile
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (!isset($_GET['id'])) {
        sendResponse(['error' => 'Missing profile ID'], 400);
    }
    
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("DELETE FROM career_profiles WHERE id=?");
    $stmt->bind_param("i", $_GET['id']);
    
    if ($stmt->execute()) {
        sendResponse(['message' => 'Career profile deleted successfully']);
    } else {
        sendResponse(['error' => 'Failed to delete career profile'], 500);
    }
}
?> 