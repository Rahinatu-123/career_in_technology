<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $host = 'localhost';
    $dbname = 'career_tech_db';
    $username = 'root';
    $password = '';
    
    // Test MySQL connection first
    $mysql = new mysqli($host, $username, $password);
    if ($mysql->connect_error) {
        throw new Exception("MySQL Connection Error: " . $mysql->connect_error);
    }
    
    // Check if database exists
    $result = $mysql->query("SHOW DATABASES LIKE '$dbname'");
    if ($result->num_rows == 0) {
        throw new Exception("Database '$dbname' does not exist. Please create it first.");
    }
    
    // Close MySQL connection
    $mysql->close();
    
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5 // Set timeout to 5 seconds
    ]);
    
    // Test the connection
    $pdo->query("SELECT 1");
    
} catch (Exception $e) {
    die(json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'details' => [
            'host' => $host,
            'dbname' => $dbname,
            'username' => $username,
            'error_code' => $e->getCode()
        ]
    ]));
} 