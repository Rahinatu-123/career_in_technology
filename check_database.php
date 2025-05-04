<?php
require_once 'config.php';

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Database connection successful\n";

// Check if users table exists and has data
$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows == 0) {
    die("Users table does not exist\n");
}
echo "Users table exists\n";

// Check if there are any users
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    echo "Warning: No users found in the database\n";
} else {
    echo "Found {$row['count']} users in the database\n";
}

// Check if the test user exists
$email = 'rahinatulawal02@gmail.com';
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo "Warning: Test user not found\n";
} else {
    echo "Test user found\n";
}

// Check API configuration
echo "\nAPI Configuration:\n";
echo "Base URL: http://localhost/ctech-web/api\n";
echo "Login endpoint: http://localhost/ctech-web/api/login.php\n";
echo "Test connection endpoint: http://localhost/ctech-web/api/test_connection.php\n"; 