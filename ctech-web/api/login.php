<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
require_once '../db/config.php';
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['email'], $data['password'])) {
        sendResponse(['error' => 'Missing required fields'], 400);
    }

    $email = $data['email'];
    $password = $data['password'];

    $conn = getDBConnection();

    $stmt = $conn->prepare("SELECT id, firstname, lastname, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            unset($user['password']); // Don't return the password hash
            sendResponse(['success' => true, 'user' => $user]);
        } else {
            sendResponse(['error' => 'Invalid credentials'], 401);
        }
    } else {
        sendResponse(['error' => 'User not found'], 404);
    }
}
?> 