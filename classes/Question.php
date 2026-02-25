<?php
// classes/Question.php
class Question {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Ajouter une question
    public function ajouter($quiz_id, $question_text, $bonne_reponse, $mauvaises_reponses, $image_url = null) {
        try {
            $query = "INSERT INTO questions (quiz_id, question_text, bonne_reponse, mauvaises_reponses, image_url) 
                      VALUES (:quiz_id, :question_text, :bonne_reponse, :mauvaises_reponses, :image_url)";
            
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':quiz_id' => $quiz_id,
                ':question_text' => $question_text,
                ':bonne_reponse' => $bonne_reponse,
                ':mauvaises_reponses' => $mauvaises_reponses,
                ':image_url' => $image_url
            ]);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // Modifier une question
    public function modifier($id, $question_text, $bonne_reponse, $mauvaises_reponses, $image_url = null) {
        try {
            $query = "UPDATE questions 
                      SET question_text = :question_text,
                          bonne_reponse = :bonne_reponse,
                          mauvaises_reponses = :mauvaises_reponses,
                          image_url = :image_url
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':id' => $id,
                ':question_text' => $question_text,
                ':bonne_reponse' => $bonne_reponse,
                ':mauvaises_reponses' => $mauvaises_reponses,
                ':image_url' => $image_url
            ]);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // Supprimer une question
    public function supprimer($id) {
        try {
            $query = "DELETE FROM questions WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([':id' => $id]);
        } catch(PDOException $e) {
            return false;
        }
    }

    // Récupérer une question par ID
    public function getById($id) {
        try {
            $query = "SELECT * FROM questions WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // Récupérer toutes les questions d’un quiz
    public function getQuestionsByQuiz($quiz_id) {
        try {
            $query = "SELECT * FROM questions WHERE quiz_id = :quiz_id ORDER BY id ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':quiz_id' => $quiz_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }
}
?>