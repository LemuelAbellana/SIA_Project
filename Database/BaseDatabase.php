<?php

abstract class BaseDatabase {
    protected $db;

    public function __construct(Database $database) {
        $this->db = $database->getConnection();
    }
}
?>
