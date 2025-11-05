<?php
class Database {
    private $host = "localhost";
    private $db_name = "biblioteca";  // debe coincidir con tu BD
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
        return $this->conn;
    }
}
?>