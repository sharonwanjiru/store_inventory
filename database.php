<?php
class Database {
    private $host = 'localhost';
    private $db = 'inventory';
    private $user = 'root';
    private $pass = '';
    public $conn;

    public function __construct() {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->db);
        if ($this->conn->connect_error) {
            die(json_encode(['error' => 'Database connection failed']));
        }
    }
}
?>