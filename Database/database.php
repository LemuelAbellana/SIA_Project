<?php

class Database {
    private $host;
    private $username;
    private $password;
    private $dbName;
    private $connection;

    public function __construct() {
        // Use environment variables for credentials
        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->username = $_ENV['DB_USERNAME'] ?? 'root';
        $this->password = $_ENV['DB_PASSWORD'] ?? '';
        $this->dbName = $_ENV['DB_NAME'] ?? 'escape_avenue';

        // Establish the database connection
        $this->connection = new mysqli($this->host, $this->username, $this->password, $this->dbName);

        if ($this->connection->connect_error) {
            error_log("Connection failed: " . $this->connection->connect_error);
            die("A database error occurred. Please try again later.");
        }

        $this->connection->set_charset('utf8mb4');
    }

    public function getConnection() {
        return $this->connection;
    }

    public function close() {
        $this->connection->close();
    }
}

?>
