<?php
// controllers/AJAXController.php
class AJAXController {
    private $conn;
    private $auth;
    private $quiz;
    private $question;
    private $result;
    
    public function __construct($db) {
        $this->conn = $db;
        $this->auth = new Auth($db);
        $this->quiz = new Quiz($db);
        $this->question = new Question($db);
        $this->result = new Result($db);
    }
    
    public function verifierConnexion() {
        if (!$this->auth->verifierSession()) {
            echo json_encode(['error' => 'Non autorisé']);
            exit();
        }
    }
    
    public function soumettreReponse() {
        $this->verifierConnexion();
        
        if (isset($_POST['question_id']) && isset($_POST['reponse'])) {
            $question_id = $_POST['question_id'];
            $reponse = $_POST['reponse'];
            
            $question = $this->question->getQuestion($question_id);
            $correct = ($reponse === $question['bonne_reponse']);
            
            echo json_encode([
                'success' => true,
                'correct' => $correct,
                'bonne_reponse' => $question['bonne_reponse']
            ]);
        } else {
            echo json_encode(['error' => 'Données manquantes']);
        }
    }
    
    public function getQuestionSuivante() {
        $this->verifierConnexion();
        
        if (isset($_POST['quiz_id']) && isset($_POST['question_actuelle'])) {
            $quiz_id = $_POST['quiz_id'];
            $question_actuelle = $_POST['question_actuelle'];
            
            $questions = $this->question->getQuestionsByQuiz($quiz_id);
            $index = array_search($question_actuelle, array_column($questions, 'id'));
            
            if ($index !== false && isset($questions[$index + 1])) {
                echo json_encode([
                    'success' => true,
                    'question' => $questions[$index + 1]
                ]);
            } else {
                echo json_encode(['fin' => true]);
            }
        } else {
            echo json_encode(['error' => 'Données manquantes']);
        }
    }
    
    public function sauvegarderProgression() {
        $this->verifierConnexion();
        
        if (isset($_POST['quiz_id']) && isset($_POST['score'])) {
            $user_id = $_SESSION['user_id'];
            $quiz_id = $_POST['quiz_id'];
            $score = $_POST['score'];
            
            if ($this->result->enregistrer($user_id, $quiz_id, $score)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['error' => 'Erreur de sauvegarde']);
            }
        } else {
            echo json_encode(['error' => 'Données manquantes']);
        }
    }
}
?>