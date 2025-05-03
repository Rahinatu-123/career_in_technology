<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'config.php';

try {
    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        firstname VARCHAR(50) NOT NULL,
        lastname VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Create tech_words table
    $pdo->exec("CREATE TABLE IF NOT EXISTS tech_words (
        id INT AUTO_INCREMENT PRIMARY KEY,
        word VARCHAR(100) NOT NULL,
        definition TEXT NOT NULL,
        category VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Create inspiring_stories table
    $pdo->exec("CREATE TABLE IF NOT EXISTS inspiring_stories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        role VARCHAR(100) NOT NULL,
        company VARCHAR(100),
        image_path VARCHAR(255),
        short_quote TEXT,
        full_story TEXT,
        audio_path VARCHAR(255),
        related_careers JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Create career_profiles table
    $pdo->exec("CREATE TABLE IF NOT EXISTS career_profiles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(100) NOT NULL,
        description TEXT NOT NULL,
        skills TEXT,
        education TEXT,
        salary_range VARCHAR(100),
        job_outlook TEXT,
        image_path VARCHAR(255),
        video_path VARCHAR(255),
        audio_path VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Add a test user if users table is empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    if ($stmt->fetchColumn() == 0) {
        $password = password_hash('test123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO users (firstname, lastname, email, password) VALUES 
            ('Test', 'User', 'test@example.com', '$password')");
    }

    echo json_encode([
        'success' => true,
        'message' => 'Tables created successfully',
        'tables' => [
            'users' => 'Created',
            'tech_words' => 'Created',
            'inspiring_stories' => 'Created',
            'career_profiles' => 'Created'
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 