<?php
require_once 'User.php';

class AdminUser extends User {
    
    public function authenticate($email, $password) {
        // Admin-specific authentication logic
        if ($this->isValidEmail($email)) {
            // Simulate DB query for admin user
            $storedPassword = '$2y$10$examplehashedpassword'; // Example hash for password
            
            if (password_verify($password, $storedPassword)) {
                return true;  // Authentication successful
            }
        }
        return false;  // Authentication failed
    }
}
?>