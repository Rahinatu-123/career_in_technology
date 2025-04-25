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

// Function to check table structure
function checkTable($conn, $tableName) {
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    if ($result->num_rows > 0) {
        echo "$tableName table exists<br>";
        
        // Check structure of the table
        $result = $conn->query("DESCRIBE $tableName");
        echo "$tableName table structure:<br>";
        while ($row = $result->fetch_assoc()) {
            echo "Column: " . $row['Field'] . ", Type: " . $row['Type'] . ", Key: " . $row['Key'] . "<br>";
        }
        
        // If it's a relationship table, show foreign key constraints
        if ($tableName === 'word_careers') {
            $result = $conn->query("
                SELECT 
                    CONSTRAINT_NAME,
                    COLUMN_NAME,
                    REFERENCED_TABLE_NAME,
                    REFERENCED_COLUMN_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_NAME = '$tableName'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            echo "<br>Foreign key constraints for $tableName:<br>";
            while ($row = $result->fetch_assoc()) {
                echo "Constraint: " . $row['CONSTRAINT_NAME'] . 
                     ", Column: " . $row['COLUMN_NAME'] . 
                     " references " . $row['REFERENCED_TABLE_NAME'] . 
                     "(" . $row['REFERENCED_COLUMN_NAME'] . ")<br>";
            }
        }
        
        echo "<br>";
    } else {
        echo "$tableName table does not exist<br><br>";
    }
}

// Check all relevant tables
checkTable($conn, 'tech_words');
checkTable($conn, 'career_profiles');
checkTable($conn, 'word_careers');

// Close connection
$conn->close();
?> 