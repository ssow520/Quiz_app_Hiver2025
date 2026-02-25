<?php
class CookieManager {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }

    // Définir un cookie sécurisé
    public function setCookie($name, $value, $expiry = null) {
        if ($expiry === null) {
            $expiry = time() + COOKIE_LIFETIME; // Utiliser la constante COOKIE_LIFETIME définie ailleurs
        }
        
        // Définir le cookie avec des paramètres de sécurité améliorés
        setcookie(
            $name,
            $value,
            [
                'expires' => $expiry,
                'path' => '/',    
                'domain' => '',            
                'secure' => true,          
                'httponly' => true,         
                'samesite' => 'Strict'      
            ]
        );
    }

    // Récupérer un cookie par son nom
    public function getCookie($name) {
        // Retourne la valeur du cookie ou null si le cookie n'existe pas
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
    }

    // Supprimer un cookie
    public function deleteCookie($name) {
        if (isset($_COOKIE[$name])) {
            // Définir un cookie expiré pour le supprimer
            setcookie(
                $name,
                '',
                [
                    'expires' => time() - 3600,
                    'path' => '/',
                    'domain' => '',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]
            );
        }
    }
}
?>