<?php
require_once 'config.php';

$tech_words = [
    [
        'word' => 'Algorithm',
        'definition' => 'A set of rules or steps used to solve a problem or perform a task.',
        'category' => 'Programming'
    ],
    [
        'word' => 'API',
        'definition' => 'A set of functions and protocols that allow different software applications to communicate with each other.',
        'category' => 'Development'
    ],
    [
        'word' => 'Bug',
        'definition' => 'An error or flaw in a computer program that causes it to produce incorrect or unexpected results.',
        'category' => 'Programming'
    ],
    [
        'word' => 'Cloud Computing',
        'definition' => 'The delivery of computing services over the internet, allowing for on-demand access to resources.',
        'category' => 'Infrastructure'
    ],
    [
        'word' => 'Database',
        'definition' => 'An organized collection of data, generally stored and accessed electronically from a computer system.',
        'category' => 'Data Management'
    ],
    [
        'word' => 'Encryption',
        'definition' => 'The process of converting information or data into a code to prevent unauthorized access.',
        'category' => 'Security'
    ],
    [
        'word' => 'Framework',
        'definition' => 'A platform for developing software applications that provides a foundation on which software developers can build programs for a specific platform.',
        'category' => 'Development'
    ],
    [
        'word' => 'Open Source',
        'definition' => 'Software with source code that anyone can inspect, modify, and enhance.',
        'category' => 'Development'
    ],
    [
        'word' => 'UI',
        'definition' => 'User Interface; the space where interactions between humans and machines occur.',
        'category' => 'Design'
    ],
    [
        'word' => 'UX',
        'definition' => 'User Experience; the overall experience a user has when interacting with a product or service.',
        'category' => 'Design'
    ]
];

// Prepare the SQL statement
$stmt = $conn->prepare("INSERT INTO tech_words (word, definition, category) VALUES (?, ?, ?)");

if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

// Insert each tech word
foreach ($tech_words as $word) {
    $stmt->bind_param("sss", $word['word'], $word['definition'], $word['category']);
    
    if (!$stmt->execute()) {
        echo "Error inserting word '{$word['word']}': " . $stmt->error . "\n";
    } else {
        echo "Successfully inserted: {$word['word']}\n";
    }
}

$stmt->close();
$conn->close();

echo "Tech words insertion completed.\n";
?> 