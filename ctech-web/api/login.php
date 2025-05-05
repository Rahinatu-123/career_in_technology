<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

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

logMessage("Login request received from: " . $_SERVER['REMOTE_ADDR']);

// TEMPORARY: Allow GET for browser testing
// if ($_SERVER['REQUEST_METHOD'] === 'GET') {
//     $_POST['email'] = $_GET['email'] ?? '';
//     $_POST['password'] = $_GET['password'] ?? '';
//     $_SERVER['REQUEST_METHOD'] = 'POST'; // Pretend it's a POST
// }

// Ensure this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logMessage("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        'success' => false,
        'error' => 'Only POST method is allowed'
    ]);
    exit;
}

try {
    // Get raw input or fallback to $_POST for GET compatibility
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    // Fallback to $_POST if raw JSON is empty (e.g., in GET-mode testing)
    if (!$data) {
        $data = $_POST;
    }

    logMessage("Input data: " . json_encode($data));

    // Validate required fields
    if (empty($data['email']) || empty($data['password'])) {
        throw new Exception('Email and password are required');
    }

    $email = trim($data['email']);
    $password = $data['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Database connection
    $host = '20.251.152.247';
    $dbname = 'career_tech_db';
    $username = 'root';
    $dbPassword = '';

    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $dbPassword);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        logMessage("Database connection successful");
    } catch (PDOException $e) {
        logMessage("Database connection failed: " . $e->getMessage());
        throw new Exception('Unable to connect to database. Please check your configuration.');
    }

    // Check if users table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'users'");
    if ($tableCheck->rowCount() == 0) {
        logMessage("Users table does not exist");
        throw new Exception('Users table does not exist. Please run create_tables.php first.');
    }

    // Lookup user
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($password, $user['password'])) {
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
