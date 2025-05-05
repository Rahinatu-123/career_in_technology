<?php
header('Content-Type: application/json');

// Test database connection
try {
    require_once 'config.php';
    echo json_encode([
        'success' => true,
        'message' => 'Database connection successful',
        'database' => [
            'host' => $host,
            'name' => $db,
            'user' => $user
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit;
}

// Test login endpoint
$loginUrl = 'http://20.251.152.247/ctech-web/api/login.php';
$testData = [
    'email' => 'rahinatulawal02@gmail.com',
    'password' => 'admin123'
];

$ch = curl_init($loginUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "\n\nLogin endpoint test:\n";
echo "HTTP Status Code: $httpCode\n";
echo "Response: $response\n";

// Test connection endpoint
$testUrl = 'http://20.251.152.247/ctech-web/api/test_connection.php';
$ch = curl_init($testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "\n\nTest connection endpoint:\n";
echo "HTTP Status Code: $httpCode\n";
echo "Response: $response\n"; 