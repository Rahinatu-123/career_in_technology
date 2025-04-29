<?php
require_once 'config.php';

// Get all tech words or filter by career
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $conn = getDBConnection();
    
    // Check if filtering by career_id
    if (isset($_GET['career_id'])) {
        $sql = "SELECT tw.* FROM tech_words tw
                INNER JOIN word_careers wc ON tw.id = wc.word_id
                WHERE wc.career_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $_GET['career_id']);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $sql = "SELECT * FROM tech_words";
        $result = $conn->query($sql);
    }
    
    $words = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Get related careers for each word
            $stmt = $conn->prepare("SELECT career_id FROM word_careers WHERE word_id = ?");
            $stmt->bind_param("i", $row['id']);
            $stmt->execute();
            $careerResult = $stmt->get_result();
            
            $relatedCareers = [];
            while($career = $careerResult->fetch_assoc()) {
                $relatedCareers[] = $career['career_id'];
            }
            
            $row['related_careers'] = $relatedCareers;
            $words[] = $row;
        }
    }
    
    sendResponse($words);
}

// Add a new tech word
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['term']) || !isset($data['definition'])) {
        sendResponse(['error' => 'Missing required fields'], 400);
    }
    
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("INSERT INTO tech_words (term, definition, example, category) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", 
        $data['term'],
        $data['definition'],
        $data['example'],
        $data['category']
    );
    
    if ($stmt->execute()) {
        $wordId = $stmt->insert_id;
        
        // Insert related careers
        if (isset($data['related_careers']) && is_array($data['related_careers'])) {
            $careerStmt = $conn->prepare("INSERT INTO word_careers (word_id, career_id) VALUES (?, ?)");
            foreach ($data['related_careers'] as $careerId) {
                $careerStmt->bind_param("ii", $wordId, $careerId);
                $careerStmt->execute();
            }
        }
        
        sendResponse(['message' => 'Tech word created successfully', 'id' => $wordId]);
    } else {
        sendResponse(['error' => 'Failed to create tech word'], 500);
    }
}

// Update a tech word
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        sendResponse(['error' => 'Missing word ID'], 400);
    }
    
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("UPDATE tech_words SET term=?, definition=?, example=?, category=? WHERE id=?");
    $stmt->bind_param("ssssi", 
        $data['term'],
        $data['definition'],
        $data['example'],
        $data['category'],
        $data['id']
    );
    
    if ($stmt->execute()) {
        // Update related careers
        if (isset($data['related_careers']) && is_array($data['related_careers'])) {
            // Delete existing relationships
            $deleteStmt = $conn->prepare("DELETE FROM word_careers WHERE word_id = ?");
            $deleteStmt->bind_param("i", $data['id']);
            $deleteStmt->execute();
            
            // Insert new relationships
            $careerStmt = $conn->prepare("INSERT INTO word_careers (word_id, career_id) VALUES (?, ?)");
            foreach ($data['related_careers'] as $careerId) {
                $careerStmt->bind_param("ii", $data['id'], $careerId);
                $careerStmt->execute();
            }
        }
        
        sendResponse(['message' => 'Tech word updated successfully']);
    } else {
        sendResponse(['error' => 'Failed to update tech word'], 500);
    }
}

// Delete a tech word
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (!isset($_GET['id'])) {
        sendResponse(['error' => 'Missing word ID'], 400);
    }
    
    $conn = getDBConnection();
    
    // Delete related careers first
    $deleteStmt = $conn->prepare("DELETE FROM word_careers WHERE word_id = ?");
    $deleteStmt->bind_param("i", $_GET['id']);
    $deleteStmt->execute();
    
    // Delete the word
    $stmt = $conn->prepare("DELETE FROM tech_words WHERE id=?");
    $stmt->bind_param("i", $_GET['id']);
    
    if ($stmt->execute()) {
        sendResponse(['message' => 'Tech word deleted successfully']);
    } else {
        sendResponse(['error' => 'Failed to delete tech word'], 500);
    }
}
?> 