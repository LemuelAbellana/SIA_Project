<?php

abstract class BaseDatabase3 {
    protected $db;

    public function __construct(mysqli $connection) {
        $this->db = $connection;
    }

    // Abstract method which must be implemented in child classes
    abstract public function getBookingSummary($limit, $offset);
}
?>
