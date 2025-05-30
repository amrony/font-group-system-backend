
<?php

class Database {
    private $host = 'localhost';
    private $dbname = 'font_db';
    private $username = 'root';
    private $password = '';
    public $conn;

    public function __construct() {
        try {
            $this->conn = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function query($query) {
        return $this->conn->query($query);
    }

    public function prepare($query) {
        return $this->conn->prepare($query);
    }
}
