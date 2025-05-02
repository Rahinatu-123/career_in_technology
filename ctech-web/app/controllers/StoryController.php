<?php
require_once __DIR__ . '/../../db/config.php';

class StoryController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllStories() {
        $sql = "SELECT * FROM inspiring_stories";
        $result = $this->conn->query($sql);
        
        $stories = [];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                // Get related careers
                $stmt = $this->conn->prepare("SELECT career_id FROM story_careers WHERE story_id = ?");
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
        
        return $stories;
    }

    public function createStory($data) {
        if (!isset($data['name']) || !isset($data['role']) || !isset($data['company']) || 
            !isset($data['short_quote']) || !isset($data['full_story'])) {
            throw new Exception('Missing required fields');
        }
        
        $stmt = $this->conn->prepare("INSERT INTO inspiring_stories (name, role, company, image_path, short_quote, full_story, audio_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
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
                $careerStmt = $this->conn->prepare("INSERT INTO story_careers (story_id, career_id) VALUES (?, ?)");
                foreach ($data['related_careers'] as $careerId) {
                    $careerStmt->bind_param("ii", $storyId, $careerId);
                    $careerStmt->execute();
                }
            }
            
            return $storyId;
        }
        
        throw new Exception('Failed to create story');
    }

    public function updateStory($data) {
        if (!isset($data['id'])) {
            throw new Exception('Missing story ID');
        }
        
        $stmt = $this->conn->prepare("UPDATE inspiring_stories SET name=?, role=?, company=?, image_path=?, short_quote=?, full_story=?, audio_path=? WHERE id=?");
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
                $deleteStmt = $this->conn->prepare("DELETE FROM story_careers WHERE story_id = ?");
                $deleteStmt->bind_param("i", $data['id']);
                $deleteStmt->execute();
                
                // Insert new relationships
                $careerStmt = $this->conn->prepare("INSERT INTO story_careers (story_id, career_id) VALUES (?, ?)");
                foreach ($data['related_careers'] as $careerId) {
                    $careerStmt->bind_param("ii", $data['id'], $careerId);
                    $careerStmt->execute();
                }
            }
            
            return true;
        }
        
        throw new Exception('Failed to update story');
    }
}
?> 