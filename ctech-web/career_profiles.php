<?php
require_once 'config.php';
require_once 'functions.php';

try {
    // Get all career profiles or search
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $conn = getDBConnection();
        
        // Build the query based on search parameters
        $where_conditions = [];
        $params = [];
        $types = "";
        
        if (isset($_GET['search'])) {
            $search = "%{$_GET['search']}%";
            $where_conditions[] = "(title LIKE ? OR description LIKE ? OR skills LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $types .= "sss";
        }
        
        if (isset($_GET['min_salary'])) {
            $where_conditions[] = "CAST(SUBSTRING_INDEX(salary_range, '-', 1) AS DECIMAL) >= ?";
            $params[] = $_GET['min_salary'];
            $types .= "d";
        }
        
        if (isset($_GET['education_level'])) {
            $where_conditions[] = "education LIKE ?";
            $params[] = "%{$_GET['education_level']}%";
            $types .= "s";
        }
        
        $sql = "SELECT * FROM career_profiles";
        if (!empty($where_conditions)) {
            $sql .= " WHERE " . implode(" AND ", $where_conditions);
        }
        
        if (!empty($params)) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($sql);
        }
        
        $profiles = [];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                // Get related tech words with their details
                $stmt = $conn->prepare("
                    SELECT tw.id, tw.word, tw.definition, tw.category 
                    FROM tech_words tw
                    INNER JOIN word_careers wc ON tw.id = wc.word_id
                    WHERE wc.career_id = ?
                ");
                $stmt->bind_param("i", $row['id']);
                $stmt->execute();
                $wordResult = $stmt->get_result();
                
                $relatedWords = [];
                while($word = $wordResult->fetch_assoc()) {
                    $relatedWords[] = [
                        'id' => $word['id'],
                        'word' => $word['word'],
                        'definition' => $word['definition'],
                        'category' => $word['category']
                    ];
                }
                
                $row['related_tech_words'] = $relatedWords;
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
} catch (Exception $e) {
    sendResponse([
        'success' => false,
        'error' => $e->getMessage()
    ], 500);
}
?> 