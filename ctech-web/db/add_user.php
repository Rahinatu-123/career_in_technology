<?php
require_once 'config.php';

// Read the SQL file
$sql = file_get_contents('users.sql');

// Execute the SQL
if ($conn->multi_query($sql)) {
    do {
        // Store first result set
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());
    
    echo "Users table created and admin user added successfully!";
} else {
    echo "Error executing SQL: " . $conn->error;
}

$conn->close();
?> 