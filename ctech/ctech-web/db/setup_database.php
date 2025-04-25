<?php
// Database configuration
$host = 'localhost:3306';
$username = 'root';
$password = '';

// Create connection without database
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Read the SQL file
$sql = file_get_contents(__DIR__ . '/database.sql');

// Execute multiple SQL statements
if ($conn->multi_query($sql)) {
    do {
        // Store first result set
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());
    
    echo "Database and tables created successfully!";
} else {
    echo "Error executing SQL: " . $conn->error;
}

// Close connection
$conn->close();
?> 