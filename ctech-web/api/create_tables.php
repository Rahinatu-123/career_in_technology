<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'config.php';

try {
    $conn = getDBConnection();
    
    // Create users table
    $conn->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        firstname VARCHAR(50) NOT NULL,
        lastname VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Create tech_words table
    $conn->exec("CREATE TABLE IF NOT EXISTS tech_words (
        id INT AUTO_INCREMENT PRIMARY KEY,
        word VARCHAR(100) NOT NULL,
        definition TEXT NOT NULL,
        category VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Create inspiring_stories table
    $conn->exec("CREATE TABLE IF NOT EXISTS inspiring_stories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        role VARCHAR(100) NOT NULL,
        company VARCHAR(100) NOT NULL,
        image_path VARCHAR(255),
        short_quote TEXT,
        full_story TEXT,
        audio_path VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Create career_profiles table
    $conn->exec("CREATE TABLE IF NOT EXISTS career_profiles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(100) NOT NULL,
        description TEXT NOT NULL,
        salary_range VARCHAR(100),
        education_requirements TEXT,
        skills_required TEXT,
        job_outlook TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Check if users table is empty and add a test user
    $stmt = $conn->query("SELECT COUNT(*) FROM users");
    if ($stmt->fetchColumn() == 0) {
        $password = password_hash('test123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, email, password) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Test', 'User', 'test@example.com', $password]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Tables created successfully'
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 