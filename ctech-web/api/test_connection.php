<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Log file for debugging
$logFile = __DIR__ . '/api_debug.log';

// Function to log messages
function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

logMessage("Test connection request received from: " . $_SERVER['REMOTE_ADDR']);

try {
    // Database connection test
    $host = '20.251.152.247';
    $dbname = 'career_tech_db';
    $username = 'root';
    $dbPassword = '';
    
    $dbConnected = false;
    $tableExists = false;
    $errorMessage = '';
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $dbPassword);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbConnected = true;
        
        // Check if users table exists
        $tableCheck = $conn->query("SHOW TABLES LIKE 'users'");
        $tableExists = ($tableCheck->rowCount() > 0);
        
        logMessage("Database connection successful, users table exists: " . ($tableExists ? "Yes" : "No"));
    } catch (PDOException $e) {
        $errorMessage = $e->getMessage();
        logMessage("Database connection failed: " . $errorMessage);
    }
    
    // Return server status information
    echo json_encode([
        'success' => true,
        'timestamp' => date('Y-m-d H:i:s'),
        'server' => [
            'php_version' => phpversion(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'remote_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
        ],
        'database' => [
            'connected' => $dbConnected,
            'users_table_exists' => $tableExists,
            'error' => $errorMessage
        ]
    ]);
    
} catch (Exception $e) {
    logMessage("Error in test connection: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>