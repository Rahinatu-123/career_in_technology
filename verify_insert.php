<?php
require_once 'config.php';

// Get the most recently inserted question
$result = $conn->query("SELECT * FROM quiz_questions ORDER BY id DESC LIMIT 1");
$question = $result->fetch_assoc();

echo "Question:\n";
echo "ID: " . $question['id'] . "\n";
echo "Text: " . $question['question'] . "\n";
echo "Options:\n";
echo "A: " . $question['option_a'] . "\n";
echo "B: " . $question['option_b'] . "\n";
echo "C: " . $question['option_c'] . "\n";
echo "D: " . $question['option_d'] . "\n";
echo "Correct: " . $question['correct_option'] . "\n\n";

// Get the mappings for this question
$result = $conn->query("SELECT * FROM quiz_results_mapping WHERE question_id = " . $question['id']);
echo "Mappings:\n";
while ($row = $result->fetch_assoc()) {
    echo "Career ID: " . $row['career_id'] . ", Weight: " . $row['weight'] . "\n";
} 