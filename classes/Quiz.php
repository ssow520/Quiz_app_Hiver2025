<?php
// classes/Quiz.php
class Quiz {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function creer($titre, $description) {
        try {
            $query = "INSERT INTO quiz (titre, description, date_creation) 
                     VALUES (:titre, :description, NOW())";
            
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':titre' => $titre,
                ':description' => $description
            ]);
            
        } catch(PDOException $e) {
            return false;
        }
    }
    
    public function modifier($id, $titre, $description) {
        try {
            $query = "UPDATE quiz 
                     SET titre = :titre, 
                         description = :description 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':id' => $id,
                ':titre' => $titre,
                ':description' => $description
            ]);
            
        } catch(PDOException $e) {
            return false;
        }
    }
    
    public function supprimer($id) {
        try {
            // Supprimer d'abord les questions associées
            $query = "DELETE FROM questions WHERE quiz_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $id]);
            
            // Puis supprimer le quiz
            $query = "DELETE FROM quiz WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([':id' => $id]);
            
        } catch(PDOException $e) {
            return false;
        }
    }
    
    public function getQuiz($id) {
        try {
            $query = "SELECT * FROM quiz WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            return false;
        }
    }
    
    public function getAllQuiz() {
        try {
            $query = "SELECT * FROM quiz ORDER BY date_creation DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            return false;
        }
    }
}
?>