<?php
class Result {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function enregistrer($user_id, $quiz_id, $score) {
        try {
            $query = "INSERT INTO results (user_id, quiz_id, score, date) 
                     VALUES (:user_id, :quiz_id, :score, NOW())";

            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':user_id' => $user_id,
                ':quiz_id' => $quiz_id,
                ':score' => $score
            ]);
        } catch(PDOException $e) {
            return false;
        }
    }

    public function getResultatsUtilisateur($user_id) {
        try {
            $query = "SELECT r.*, q.titre as quiz_titre, r.score, r.date, r.quiz_id
                     FROM results r
                     JOIN quiz q ON r.quiz_id = q.id
                     WHERE r.user_id = :user_id
                     ORDER BY r.date DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([':user_id' => $user_id]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];  // Retourner un tableau vide au lieu de false
        }
    }

    public function getResultatsQuiz($quiz_id) {
        try {
            $query = "SELECT r.*, u.nom as utilisateur, r.score, r.date
                     FROM results r
                     JOIN users u ON r.user_id = u.id
                     WHERE r.quiz_id = :quiz_id
                     ORDER BY r.score DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([':quiz_id' => $quiz_id]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }

    public function getMoyenneQuiz($quiz_id) {
        try {
            $query = "SELECT AVG(score) as moyenne
                     FROM results
                     WHERE quiz_id = :quiz_id";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([':quiz_id' => $quiz_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? $result['moyenne'] : 0;
        } catch(PDOException $e) {
            return 0;
        }
    }
}
