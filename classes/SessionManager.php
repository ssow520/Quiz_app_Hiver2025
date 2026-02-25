<?php
// classes/SessionManager.php
class SessionManager {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function demarrerSession() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Régénérer l'ID de session pour prévenir la fixation de session
        if (!isset($_SESSION['last_regeneration'])) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
    
    public function setSession($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    public function getSession($key) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }
    
    public function supprimerSession($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
            return true;
        }
        return false;
    }
    
    public function detruireSession() {
        session_unset();
        session_destroy();
        
        // Supprimer le cookie de session
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
    }
    
    public function verifierExpiration() {
        if (isset($_SESSION['last_activity']) && 
            (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
            $this->detruireSession();
            return false;
        }
        
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    public function regenererSession() {
        // Régénérer l'ID de session toutes les 30 minutes
        if (isset($_SESSION['last_regeneration']) && 
            (time() - $_SESSION['last_regeneration'] > 1800)) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
}
?>