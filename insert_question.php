<?php
require_once 'config.php';

// Insert the question
$stmt = $conn->prepare("INSERT INTO quiz_questions (question, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?)");
$question = "Which school subject do you enjoy the most?";
$option_a = "Art or Design";
$option_b = "Math or Science";
$option_c = "Literature or Languages";
$option_d = "Social Studies or Business";
$correct_option = "A";

$stmt->bind_param("ssssss", $question, $option_a, $option_b, $option_c, $option_d, $correct_option);
$stmt->execute();
$question_id = $stmt->insert_id;

// Insert the mappings
$mappings = [
    [3, 2],  // UI/UX Designer
    [9, 2],  // Game Developer
    [16, 2], // AR/VR Developer
    [2, 2],  // Data Scientist
    [5, 2],  // AI Engineer
    [19, 2], // Machine Learning Engineer
    [1, 2],  // Software Developer
    [14, 2], // Technical Writer
    [18, 2], // IT Consultant
    [13, 2], // IT Project Manager
    [6, 2]   // Cloud Architect
];

$stmt = $conn->prepare("INSERT INTO quiz_results_mapping (question_id, career_id, weight) VALUES (?, ?, ?)");
foreach ($mappings as $mapping) {
    $stmt->bind_param("iii", $question_id, $mapping[0], $mapping[1]);
    $stmt->execute();
}

echo "Question and mappings inserted successfully! Question ID: " . $question_id; 