<?php
// Database configuration
$host = 'localhost:3306';
$username = 'root';
$password = '';
$database = 'career_tech_db';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL to create career_profiles table
$sql = "CREATE TABLE IF NOT EXISTS career_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    skills TEXT NOT NULL,
    education TEXT NOT NULL,
    salary_range VARCHAR(255) NOT NULL,
    job_outlook TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB";

// Execute SQL
if ($conn->query($sql) === TRUE) {
    echo "Table career_profiles created successfully<br>";
    
    // Insert a sample career profile
    $sample_sql = "INSERT INTO career_profiles (title, description, skills, education, salary_range, job_outlook) 
                   VALUES ('Software Developer', 'Develops and maintains software applications', 
                   'Programming, Problem Solving, Teamwork', 'Bachelor\'s in Computer Science', 
                   'GHC 3,000 - GHC 8,000', 'High demand in Ghana')";
    
    if ($conn->query($sample_sql) === TRUE) {
        echo "Sample career profile inserted successfully";
    } else {
        echo "Error inserting sample career profile: " . $conn->error;
    }
} else {
    echo "Error creating table: " . $conn->error;
}

// Close connection
$conn->close();
?> 