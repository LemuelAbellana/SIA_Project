<?php
require_once 'Database.php';

class Customer extends Database {
    public function getCustomerByEmail($email) {
        $query = "SELECT * FROM customer WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function createCustomer($name, $email, $contact_number) {
        $query = "INSERT INTO customer (name, email, contact_number) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('sss', $name, $email, $contact_number);
        $stmt->execute();
        return $stmt->insert_id;
    }
}
?>
