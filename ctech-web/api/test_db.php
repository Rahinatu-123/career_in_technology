<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'config.php';

try {
    // Test database connection
    $pdo->query("SELECT 1");
    
    // Get all tables in the database
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    // Get table structures
    $tableStructures = [];
    foreach ($tables as $table) {
        $columns = $pdo->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
        $tableStructures[$table] = $columns;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Database connection successful',
        'tables' => $tables,
        'structures' => $tableStructures
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
} 