<?php
// create_test_user.php
header('Content-Type: application/json');

try {
    // Database connection
    $host = 'localhost';
    $dbname = 'career_tech_db';
    $username = 'root';
    $dbPassword = '';
    
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $dbPassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create users table if not exists
    $conn->exec("CREATE TABLE IF NOT EXISTS users (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        firstname VARCHAR(50) NOT NULL,
        lastname VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Test user data
    $firstname ="Rahinatu";
    $lastname = "Lawal";
    $email = "rahinatulawal02@gmail.com";
    $password = password_hash("admin123", PASSWORD_DEFAULT);
    
    // Insert test user
    $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, email, password) 
                           VALUES (:firstname, :lastname, :email, :password)
                           ON DUPLICATE KEY UPDATE 
                           firstname = :firstname, lastname = :lastname, password = :password");
    
    $stmt->bindParam(':firstname', $firstname);
    $stmt->bindParam(':lastname', $lastname);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Test user created',
        'user' => [
            'email' => $email,
            'firstname' => $firstname,
            'lastname' => $lastname
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>