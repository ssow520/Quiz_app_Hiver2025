<?php
require_once '../classes/Auth.php';
require_once '../classes/Quiz.php';
require_once '../classes/Question.php';
require_once '../classes/Result.php';

// controllers/QuizController.php
class QuizController {
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
    
    public function verifierAuthentification() {
        $user_id = $this->auth->verifierSession();
        if (!$user_id) {
            header('Location: ' . BASE_URL . '/pages/login.php');
            exit();
        }
        return $user_id;
    }
    
    public function getQuizDisponibles() {
        $this->verifierAuthentification();
        return $this->quiz->getAllQuiz();
    }
    
    public function commencerQuiz($quiz_id) {
        $user_id = $this->verifierAuthentification();
        
        $quiz = $this->quiz->getQuiz($quiz_id);
        if (!$quiz) {
            return ['error' => 'Quiz non trouvé'];
        }
        
        $questions = $this->question->getQuestionsByQuiz($quiz_id);
        if (empty($questions)) {
            return ['error' => 'Aucune question disponible'];
        }
        
        return [
            'quiz' => $quiz,
            'questions' => $questions,
            'total_questions' => count($questions)
        ];
    }
    
    public function verifierReponse($question_id, $reponse) {
        $this->verifierAuthentification();
        
        $question = $this->question->getQuestion($question_id);
        if (!$question) {
            return ['error' => 'Question non trouvée'];
        }
        
        $correct = ($reponse === $question['bonne_reponse']);
        return [
            'correct' => $correct,
            'bonne_reponse' => $question['bonne_reponse']
        ];
    }
    
    public function terminerQuiz($quiz_id, $score) {
        $user_id = $this->verifierAuthentification();
        
        if ($this->result->enregistrer($user_id, $quiz_id, $score)) {
            return [
                'success' => true,
                'message' => 'Score enregistré avec succès',
                'score' => $score
            ];
        }
        
        return ['error' => 'Erreur lors de l\'enregistrement du score'];
    }
    
    public function getResultatsUtilisateur() {
        $user_id = $this->verifierAuthentification();
        return $this->result->getResultatsUtilisateur($user_id);
    }
    
    public function getDetailsQuiz($quiz_id) {
        $this->verifierAuthentification();
        
        $quiz = $this->quiz->getQuiz($quiz_id);
        if (!$quiz) {
            return ['error' => 'Quiz non trouvé'];
        }
        
        return [
            'quiz' => $quiz,
            'questions' => $this->question->getQuestionsByQuiz($quiz_id)
        ];
    }
}
?>