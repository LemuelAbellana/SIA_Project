<?php
// LoginDatabase.php

class LoginDatabase {
    private $conn;

    // Constructor accepts the database connection
    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    // Function to get user by email
    public function getUserByEmail($email) {
        $sql = "SELECT admin_id, email, password FROM admins WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Database error: " . $this->conn->error);
        }
        
        $stmt->bind_param("s", $email);  // Bind email parameter
        $stmt->execute();
        $result = $stmt->get_result();

        // Return the user data if found, else return null
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    }
}
?>
