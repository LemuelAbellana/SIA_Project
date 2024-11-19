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

        $this->connect();
    }

    /**
     * Establish the database connection.
     */
    private function connect() {
        $this->connection = new mysqli($this->host, $this->username, $this->password, $this->dbName);

        if ($this->connection->connect_error) {
            error_log("Database connection failed: " . $this->connection->connect_error);
            die("Database connection failed. Please try again later.");
        }

        $this->connection->set_charset('utf8mb4');
    }

    /**
     * Get the current database connection.
     *
     * @return mysqli
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Close the database connection.
     */
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}

?>
