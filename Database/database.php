<?php
class Database {
    private $hostname = "localhost";
    private $dbUser = "root";
    private $dbPassword = "";
    private $dbName = "escape_avenue";
    protected $conn;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        $this->conn = new mysqli($this->hostname, $this->dbUser, $this->dbPassword, $this->dbName);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        $this->conn->set_charset("utf8");
    }

    public function getConnection() {
        return $this->conn;
    }

    public function close() {
        $this->conn->close();
    }
}
?>
