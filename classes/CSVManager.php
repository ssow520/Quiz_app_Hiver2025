<?php
// classes/CSVManager.php
class CSVManager {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Importer des questions depuis un fichier CSV pour un quiz donné.
     * 
     * @param string $fichier Chemin du fichier CSV
     * @param int $quiz_id ID du quiz auquel associer les questions
     * @return bool
     */
    public function importerQuestions($fichier, $quiz_id) {
        if (!file_exists($fichier)) {
            return false;
        }
        
        try {
            $handle = fopen($fichier, "r");
            if (!$handle) return false;

            // Optionnel : ignorer l'en-tête si présent
            $header = fgetcsv($handle, 1000, ","); 

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if (count($data) >= 3) {
                    $question_text = trim($data[0]);
                    $bonne_reponse = trim($data[1]);
                    $mauvaises_reponses = trim($data[2]);

                    $query = "INSERT INTO questions (quiz_id, question_text, bonne_reponse, mauvaises_reponses) 
                              VALUES (:quiz_id, :question_text, :bonne_reponse, :mauvaises_reponses)";
                    $stmt = $this->conn->prepare($query);
                    $stmt->execute([
                        ':quiz_id' => $quiz_id,
                        ':question_text' => $question_text,
                        ':bonne_reponse' => $bonne_reponse,
                        ':mauvaises_reponses' => $mauvaises_reponses
                    ]);
                }
            }
            
            fclose($handle);
            return true;
            
        } catch(PDOException $e) {
            return false;
        }
    }
    
    /**
     * Exporter les résultats d’un quiz en CSV
     * 
     * @param int|null $quiz_id Si null, exporter tous les résultats
     * @return string|false Nom du fichier créé ou false si erreur
     */
    public function exporterResultats($quiz_id = null) {
        try {
            $query = "SELECT u.nom, q.titre, r.score, r.date 
                      FROM results r
                      JOIN users u ON r.user_id = u.id 
                      JOIN quiz q ON r.quiz_id = q.id";
            
            $params = [];
            if ($quiz_id !== null) {
                $query .= " WHERE r.quiz_id = :quiz_id";
                $params[':quiz_id'] = $quiz_id;
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            $filename = "resultats_" . date("Y-m-d_H-i-s") . ".csv";
            $output = fopen($filename, "w");
            
            fputcsv($output, ['Nom', 'Quiz', 'Score', 'Date']);
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                fputcsv($output, $row);
            }
            
            fclose($output);
            return $filename;
            
        } catch(PDOException $e) {
            return false;
        }
    }
}
?>