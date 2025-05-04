<?php
require_once 'config.php';

// Get all questions
$result = $conn->query("SELECT id, question FROM quiz_questions ORDER BY id");
echo "Current Questions in Database:\n";
while ($row = $result->fetch_assoc()) {
    echo "ID: " . $row['id'] . " - " . $row['question'] . "\n";
} 