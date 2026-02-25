<?php
// classes/User.php
class User {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function inscription($nom, $email, $password, $role) {
        try {
            // Vérifier si l'email existe déjà
            $query = "SELECT id FROM users WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':email' => $email]);
    
            if ($stmt->rowCount() > 0) {
                return false;
            }
    
            // Hacher le mot de passe
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
    
            // Insérer le nouvel utilisateur
            $query = "INSERT INTO users (nom, email, mot_de_passe, role, date_inscription)
                     VALUES (:nom, :email, :password, :role, NOW())";
    
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':nom' => $nom,
                ':email' => $email,
                ':password' => $password_hash,
                ':role' => $role
            ]);
    
        } catch(PDOException $e) {
            return false;
        }
    }
    
    
    public function connexion($email, $password) {
        try {
            $query = "SELECT * FROM users WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':email' => $email]);
            
            if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (password_verify($password, $user['mot_de_passe'])) {
                    unset($user['mot_de_passe']); // Ne pas renvoyer le mot de passe
                    return $user;
                }
            }
            return false;
            
        } catch(PDOException $e) {
            return false;
        }
    }
    
    public function modifier($id, $nom, $email, $role) {
        try {
            $query = "UPDATE users 
                      SET nom = :nom, 
                          email = :email, 
                          role = :role 
                      WHERE id = :id";
    
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':id' => $id,
                ':nom' => $nom,
                ':email' => $email,
                ':role' => $role
            ]);
    
        } catch(PDOException $e) {
            return false;
        }
    }
    
    
    public function getUser($id) {
        try {
            $query = "SELECT id, nom, email, role, date_inscription 
                     FROM users WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            return false;
        }
    }
}
?>