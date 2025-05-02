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

// SQL to create word_careers table
$sql = "CREATE TABLE IF NOT EXISTS word_careers (
    word_id INT NOT NULL,
    career_id INT NOT NULL,
    PRIMARY KEY (word_id, career_id),
    FOREIGN KEY (word_id) REFERENCES tech_words(id) ON DELETE CASCADE,
    FOREIGN KEY (career_id) REFERENCES career_profiles(id) ON DELETE CASCADE
)";

// Execute SQL
if ($conn->query($sql) === TRUE) {
    echo "Table word_careers created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

// Close connection
$conn->close();
?> 