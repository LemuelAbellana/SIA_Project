<?php

class Database {
    private $host = 'localhost'; // Your database host
    private $username = 'root';  // Your database username
    private $password = '';      // Your database password
    private $dbName = 'escape_avenue'; // Your database name
    private $connection;

    public function __construct() {
        $this->connection = new mysqli($this->host, $this->username, $this->password, $this->dbName);
        
        if ($this->connection->connect_error) {
            // Log the connection error and stop script execution
            error_log("Connection failed: " . $this->connection->connect_error);
            die("Connection failed: " . $this->connection->connect_error);
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    public function close() {
        $this->connection->close();
    }
}
?>
