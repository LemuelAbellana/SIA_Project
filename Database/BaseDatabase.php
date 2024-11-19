<?php

abstract class BaseDatabase {
    protected $db;

    public function __construct(mysqli $connection) {
        $this->db = $connection;
    }

    abstract protected function getAll($limit, $offset, $search);
    abstract protected function deleteById($id);
}


?>
