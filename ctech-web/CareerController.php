<?php
require_once __DIR__ . '/config.php';

class CareerController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getCareers($filter = '') {
        $query = "SELECT * FROM career_profiles";
        if (!empty($filter)) {
            $query .= " WHERE title LIKE ?";
            $filter = $filter . '%';
        }

        $stmt = $this->conn->prepare($query);
        if (!empty($filter)) {
            $stmt->bind_param("s", $filter);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $careers = [];
        while ($row = $result->fetch_assoc()) {
            $careers[] = $row;
        }

        return $careers;
    }

    public function getCareerById($id) {
        $query = "SELECT * FROM career_profiles WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
?> 