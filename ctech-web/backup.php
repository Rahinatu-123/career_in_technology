<?php
// Start session
session_start();

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include database configuration
require_once 'config.php';

// Set headers for file download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="career_tech_db_backup_' . date('Y-m-d_H-i-s') . '.sql"');

// Get all tables
$tables = array();
$result = mysqli_query($conn, "SHOW TABLES");
while ($row = mysqli_fetch_row($result)) {
    $tables[] = $row[0];
}

$output = "-- CTech Database Backup\n";
$output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
$output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
$output .= "START TRANSACTION;\n";
$output .= "SET time_zone = \"+00:00\";\n\n";

// Process each table
foreach ($tables as $table) {
    // Get create table statement
    $result = mysqli_query($conn, "SHOW CREATE TABLE `$table`");
    $row = mysqli_fetch_row($result);
    $output .= "\n\n" . $row[1] . ";\n\n";
    
    // Get table data
    $result = mysqli_query($conn, "SELECT * FROM `$table`");
    while ($row = mysqli_fetch_row($result)) {
        $output .= "INSERT INTO `$table` VALUES(";
        for ($i = 0; $i < count($row); $i++) {
            $row[$i] = addslashes($row[$i]);
            $row[$i] = str_replace("\n", "\\n", $row[$i]);
            if (isset($row[$i])) {
                $output .= '"' . $row[$i] . '"';
            } else {
                $output .= '""';
            }
            if ($i < (count($row) - 1)) {
                $output .= ',';
            }
        }
        $output .= ");\n";
    }
}

$output .= "\n\nCOMMIT;";

// Output the backup
echo $output;
exit;
?> 