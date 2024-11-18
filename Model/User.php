<?php
abstract class User {
    protected $email;
    protected $password;

    // Abstract method to be implemented by subclasses
    abstract public function authenticate($email, $password);

    // Shared method for email validation
    protected function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
?>