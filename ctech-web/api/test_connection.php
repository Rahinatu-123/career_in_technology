<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Test if we can reach this endpoint
echo json_encode([
    'success' => true,
    'message' => 'API endpoint is reachable',
    'timestamp' => date('Y-m-d H:i:s'),
    'server' => $_SERVER['SERVER_NAME'],
    'ip' => $_SERVER['SERVER_ADDR']
]); 