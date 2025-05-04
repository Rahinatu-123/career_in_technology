<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify Quiz Data</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Quiz Questions</h1>
    <table>
        <tr>
            <th>ID</th>
            <th>Question</th>
            <th>Option A</th>
            <th>Option B</th>
            <th>Option C</th>
            <th>Option D</th>
            <th>Correct Option</th>
        </tr>
        <?php
        $result = $conn->query("SELECT * FROM quiz_questions ORDER BY id DESC");
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['question']) . "</td>";
            echo "<td>" . htmlspecialchars($row['option_a']) . "</td>";
            echo "<td>" . htmlspecialchars($row['option_b']) . "</td>";
            echo "<td>" . htmlspecialchars($row['option_c']) . "</td>";
            echo "<td>" . htmlspecialchars($row['option_d']) . "</td>";
            echo "<td>" . $row['correct_option'] . "</td>";
            echo "</tr>";
        }
        ?>
    </table>

    <h1>Quiz Results Mapping</h1>
    <table>
        <tr>
            <th>Question ID</th>
            <th>Career ID</th>
            <th>Weight</th>
        </tr>
        <?php
        $result = $conn->query("SELECT * FROM quiz_results_mapping ORDER BY question_id, career_id");
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['question_id'] . "</td>";
            echo "<td>" . $row['career_id'] . "</td>";
            echo "<td>" . $row['weight'] . "</td>";
            echo "</tr>";
        }
        ?>
    </table>
</body>
</html> 