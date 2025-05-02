<?php
require_once __DIR__ . '/../../db/config.php';

class Career {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllProfiles() {
        $sql = "SELECT * FROM career_profiles";
        $result = $this->conn->query($sql);
        
        $profiles = [];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $profiles[] = $row;
            }
        }
        
        return $profiles;
    }

    public function createProfile($data) {
        if (!isset($data['title']) || !isset($data['description'])) {
            throw new Exception('Missing required fields');
        }
        
        $stmt = $this->conn->prepare("INSERT INTO career_profiles (title, description, skills, education, salary_range, job_outlook) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", 
            $data['title'],
            $data['description'],
            $data['skills'],
            $data['education'],
            $data['salary_range'],
            $data['job_outlook']
        );
        
        if ($stmt->execute()) {
            return $stmt->insert_id;
        }
        
        throw new Exception('Failed to create career profile');
    }

    public function updateProfile($data) {
        if (!isset($data['id'])) {
            throw new Exception('Missing profile ID');
        }
        
        $stmt = $this->conn->prepare("UPDATE career_profiles SET title=?, description=?, skills=?, education=?, salary_range=?, job_outlook=? WHERE id=?");
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
            return true;
        }
        
        throw new Exception('Failed to update career profile');
    }

    public function deleteProfile($id) {
        $stmt = $this->conn->prepare("DELETE FROM career_profiles WHERE id=?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            return true;
        }
        
        throw new Exception('Failed to delete career profile');
    }
}
?> 