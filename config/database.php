<?php
// config/database.php
class Database {
    private $host = 'localhost';
    private $db_name = 'quiz_app';
    private $username = 'root';
    private $password = '';
    private $conn = null;
    
    public function getConnection() {
        try {
            if ($this->conn === null) {
                $this->conn = new PDO(
                    "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                    $this->username,
                    $this->password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                    ]
                );
            }
            return $this->conn;
            
        } catch(PDOException $e) {
            echo "Erreur de connexion : " . $e->getMessage();
            return null;
        }
    }
    
    public function closeConnection() {
        $this->conn = null;
    }
}
?>