<?php
$host = 'localhost';      // Database host
$db   = 'career_tech_db';  // Database name for local development
$user = 'root';            // Database username (default for XAMPP)
$password = '';            // Database password (default empty for XAMPP)

// Create connection
$conn = new mysqli($host, $user, $password, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?> 