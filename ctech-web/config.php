<?php
$host = '20.251.152.247';      // Database host
$db   = 'career_tech_db';  // Your local database name
$user = 'root';          // XAMPP default username
$password = '';          // XAMPP default password (empty)

 
// Create connection
$conn = new mysqli($host, $user, $password, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
