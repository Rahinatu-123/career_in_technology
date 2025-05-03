<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log file for debugging
$logFile = __DIR__ . '/login_debug.log';

// Function to log messages
function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

try {
    // Get raw input data
    $rawInput = file_get_contents('php://input');
    logMessage("Raw input: $rawInput");
    
    // Parse JSON input
    $data = json_decode($rawInput, true);
    
    if (!$data) {
        throw new Exception('Invalid JSON input');
    }
    
    // Validate required fields
    if (empty($data['email']) || empty($data['password'])) {
        throw new Exception('Email and password are required');
    }
    
    $email = $data['email'];
    $password = $data['password'];
    
    // Database connection
    $host = 'localhost';
    $dbname = 'ctech';
    $username = 'root';
    $dbPassword = '';
    
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $dbPassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if users table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'users'");
    if ($tableCheck->rowCount() == 0) {
        throw new Exception('Users table does not exist. Please run create_tables.php first.');
    }
    
    // Prepare and execute query
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Remove sensitive data before sending response
            unset($user['password']);
            
            logMessage("Login successful for email: $email");
            echo json_encode([
                'success' => true,
                'user' => $user
            ]);
        } else {
            logMessage("Invalid password for email: $email");
            echo json_encode([
                'success' => false,
                'error' => 'Invalid email or password'
            ]);
        }
    } else {
        logMessage("User not found for email: $email");
        echo json_encode([
            'success' => false,
            'error' => 'Invalid email or password'
        ]);
    }
    
} catch (PDOException $e) {
    logMessage("Database error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    logMessage("Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 