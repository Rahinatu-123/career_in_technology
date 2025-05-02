<?php
require_once '../db/config.php';
require_once 'functions.php';

try {
    $conn = getDBConnection();
    
    // Get filter parameter
    $filter = isset($_GET['filter']) ? $_GET['filter'] : '';

    // Prepare the query
    $query = "SELECT * FROM career_profiles";
    if (!empty($filter)) {
        $query .= " WHERE title LIKE ?";
        $filter = $filter . '%';
    }

    $stmt = $conn->prepare($query);
    if (!empty($filter)) {
        $stmt->bind_param("s", $filter);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $careers = [];
    while ($row = $result->fetch_assoc()) {
        $careers[] = $row;
    }

    sendResponse([
        'success' => true,
        'data' => $careers
    ]);
} catch (Exception $e) {
    sendResponse([
        'success' => false,
        'error' => $e->getMessage()
    ], 500);
}
?> 