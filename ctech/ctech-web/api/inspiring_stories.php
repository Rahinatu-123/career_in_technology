<?php
require_once 'config.php';

// Get all inspiring stories
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $conn = getDBConnection();
    
    $sql = "SELECT * FROM inspiring_stories";
    $result = $conn->query($sql);
    
    $stories = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Get related careers
            $stmt = $conn->prepare("SELECT career_id FROM story_careers WHERE story_id = ?");
            $stmt->bind_param("i", $row['id']);
            $stmt->execute();
            $careerResult = $stmt->get_result();
            
            $relatedCareers = [];
            while($career = $careerResult->fetch_assoc()) {
                $relatedCareers[] = $career['career_id'];
            }
            
            $row['related_careers'] = $relatedCareers;
            $stories[] = $row;
        }
    }
    
    sendResponse($stories);
}

// Add a new inspiring story
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['name']) || !isset($data['role']) || !isset($data['company']) || 
        !isset($data['short_quote']) || !isset($data['full_story'])) {
        sendResponse(['error' => 'Missing required fields'], 400);
    }
    
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("INSERT INTO inspiring_stories (name, role, company, image_path, short_quote, full_story, audio_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", 
        $data['name'],
        $data['role'],
        $data['company'],
        $data['image_path'],
        $data['short_quote'],
        $data['full_story'],
        $data['audio_path']
    );
    
    if ($stmt->execute()) {
        $storyId = $stmt->insert_id;
        
        // Insert related careers
        if (isset($data['related_careers']) && is_array($data['related_careers'])) {
            $careerStmt = $conn->prepare("INSERT INTO story_careers (story_id, career_id) VALUES (?, ?)");
            foreach ($data['related_careers'] as $careerId) {
                $careerStmt->bind_param("ii", $storyId, $careerId);
                $careerStmt->execute();
            }
        }
        
        sendResponse(['message' => 'Story created successfully', 'id' => $storyId]);
    } else {
        sendResponse(['error' => 'Failed to create story'], 500);
    }
}

// Update an inspiring story
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        sendResponse(['error' => 'Missing story ID'], 400);
    }
    
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("UPDATE inspiring_stories SET name=?, role=?, company=?, image_path=?, short_quote=?, full_story=?, audio_path=? WHERE id=?");
    $stmt->bind_param("sssssssi", 
        $data['name'],
        $data['role'],
        $data['company'],
        $data['image_path'],
        $data['short_quote'],
        $data['full_story'],
        $data['audio_path'],
        $data['id']
    );
    
    if ($stmt->execute()) {
        // Update related careers
        if (isset($data['related_careers']) && is_array($data['related_careers'])) {
            // Delete existing relationships
            $deleteStmt = $conn->prepare("DELETE FROM story_careers WHERE story_id = ?");
            $deleteStmt->bind_param("i", $data['id']);
            $deleteStmt->execute();
            
            // Insert new relationships
            $careerStmt = $conn->prepare("INSERT INTO story_careers (story_id, career_id) VALUES (?, ?)");
            foreach ($data['related_careers'] as $careerId) {
                $careerStmt->bind_param("ii", $data['id'], $careerId);
                $careerStmt->execute();
            }
        }
        
        sendResponse(['message' => 'Story updated successfully']);
    } else {
        sendResponse(['error' => 'Failed to update story'], 500);
    }
}

// Delete an inspiring story
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (!isset($_GET['id'])) {
        sendResponse(['error' => 'Missing story ID'], 400);
    }
    
    $conn = getDBConnection();
    
    // Delete related careers first
    $deleteStmt = $conn->prepare("DELETE FROM story_careers WHERE story_id = ?");
    $deleteStmt->bind_param("i", $_GET['id']);
    $deleteStmt->execute();
    
    // Delete the story
    $stmt = $conn->prepare("DELETE FROM inspiring_stories WHERE id=?");
    $stmt->bind_param("i", $_GET['id']);
    
    if ($stmt->execute()) {
        sendResponse(['message' => 'Story deleted successfully']);
    } else {
        sendResponse(['error' => 'Failed to delete story'], 500);
    }
}
?> 