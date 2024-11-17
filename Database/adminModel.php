<?php
require_once "Database.php";

class AdminModel {
    private $db;

    public function __construct() {
        // Use the Database class to establish a connection
        $this->db = (new Database())->getConnection();
    }

    /**
     * Check if an email already exists in the database.
     *
     * @param string $email
     * @return bool
     */
    public function isEmailExists($email) {
        $stmt = $this->db->prepare("SELECT admin_id FROM admins WHERE email = ?");
        if (!$stmt) {
            throw new Exception("Database prepare failed: " . $this->db->error);
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    /**
     * Register a new admin.
     *
     * @param string $fullName
     * @param string $email
     * @param string $passwordHash
     * @return bool
     */
    public function registerAdmin($fullName, $email, $passwordHash) {
        $stmt = $this->db->prepare("INSERT INTO admins (full_name, email, password) VALUES (?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Database prepare failed: " . $this->db->error);
        }
        $stmt->bind_param("sss", $fullName, $email, $passwordHash);
        return $stmt->execute();
    }
}
?>
