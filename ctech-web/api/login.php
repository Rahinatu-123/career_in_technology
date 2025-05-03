<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

// Log the request
file_put_contents('login_debug.log', date('Y-m-d H:i:s') . " - Login attempt\n", FILE_APPEND);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get raw input
    $rawInput = file_get_contents('php://input');
    file_put_contents('login_debug.log', "Raw input: " . $rawInput . "\n", FILE_APPEND);
    
    $data = json_decode($rawInput, true);
    
    if (!isset($data['email']) || !isset($data['password'])) {
        file_put_contents('login_debug.log', "Missing email or password\n", FILE_APPEND);
        echo json_encode([
            'success' => false,
            'error' => 'Email and password are required',
            'received_data' => $data
        ]);
        exit;
    }

    $email = $data['email'];
    $password = $data['password'];

    file_put_contents('login_debug.log', "Attempting login for email: $email\n", FILE_APPEND);

    try {
        // Check if users table exists
        $tableExists = $pdo->query("SHOW TABLES LIKE 'users'")->rowCount() > 0;
        if (!$tableExists) {
            throw new Exception("Users table does not exist");
        }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        file_put_contents('login_debug.log', "User found: " . ($user ? 'yes' : 'no') . "\n", FILE_APPEND);

        if ($user && password_verify($password, $user['password'])) {
            // Remove sensitive data before sending
            unset($user['password']);
            
            file_put_contents('login_debug.log', "Login successful for email: $email\n", FILE_APPEND);
            
            echo json_encode([
                'success' => true,
                'user' => $user
            ]);
        } else {
            file_put_contents('login_debug.log', "Invalid credentials for email: $email\n", FILE_APPEND);
            
            echo json_encode([
                'success' => false,
                'error' => 'Invalid email or password'
            ]);
        }
    } catch (Exception $e) {
        file_put_contents('login_debug.log', "Error: " . $e->getMessage() . "\n", FILE_APPEND);
        
        echo json_encode([
            'success' => false,
            'error' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    file_put_contents('login_debug.log', "Invalid request method: " . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);
    
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method'
    ]);
} 