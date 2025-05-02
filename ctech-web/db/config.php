<?php
$host = 'localhost';      // Database host
$db   = 'webtech_fall2024_rahinatu_lawal';  // Database name
$user = 'root';            // Database username (default for XAMPP)
$password = 'studyNest4*';                // Database password (default empty for XAMPP)

// Create connection
$conn = new mysqli($host, $user, $password, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?> 