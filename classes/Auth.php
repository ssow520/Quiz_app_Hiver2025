<?php
// classes/Auth.php
class Auth {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function creerSession($user_id, $remember = false) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['last_activity'] = time();
        
        if($remember) {
            $token = bin2hex(random_bytes(32));
            $expiration = date('Y-m-d H:i:s', strtotime('+30 days'));
            
            $query = "INSERT INTO sessions (user_id, session_token, expiration) 
                     VALUES (:user_id, :token, :expiration)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':user_id' => $user_id,
                ':token' => $token,
                ':expiration' => $expiration
            ]);
            
            setcookie('remember_token', $token, time() + COOKIE_LIFETIME, '/', '', true, true);
        }
    }
    
    public function verifierSession(): int|false {  
        if(isset($_SESSION['user_id'])) {
            if(time() - $_SESSION['last_activity'] > SESSION_LIFETIME) {
                $this->deconnexion();
                return false;
            }
            $_SESSION['last_activity'] = time();
            return $_SESSION['user_id'];
        }
        
        // Vérifier le cookie "Se souvenir de moi"
        if(isset($_COOKIE['remember_token'])) {
            $query = "SELECT user_id FROM sessions 
                     WHERE session_token = :token 
                     AND expiration > NOW()";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':token' => $_COOKIE['remember_token']]);
            
            if($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->creerSession($result['user_id'], true);
                return $result['user_id'];
            }
        }
        
        return false;
    }
    
    public function deconnexion() {
        session_destroy();
        
        if(isset($_COOKIE['remember_token'])) {
            $query = "DELETE FROM sessions WHERE session_token = :token";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':token' => $_COOKIE['remember_token']]);
            
            setcookie('remember_token', '', time() - 3600, '/');
        }
    }
    
    public function estAdmin($user_id) {
        $query = "SELECT role FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $user_id]);
        
        if($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $user['role'] === 'admin';
        }
        return false;
    }
}
?>