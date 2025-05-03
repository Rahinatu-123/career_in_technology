<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'config.php';

try {
    // Get all tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    // Get table structures
    $tableStructures = [];
    foreach ($tables as $table) {
        $columns = $pdo->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
        $tableStructures[$table] = $columns;
    }
    
    // Check for required tables
    $requiredTables = ['users', 'tech_words', 'inspiring_stories', 'career_profiles'];
    $missingTables = array_diff($requiredTables, $tables);
    
    echo json_encode([
        'success' => true,
        'message' => 'Database check completed',
        'database' => 'career_tech_db',
        'tables' => $tables,
        'structures' => $tableStructures,
        'missing_tables' => $missingTables
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 