<?php
require_once '../classes/Auth.php';
require_once '../classes/Quiz.php';
require_once '../classes/Question.php';
require_once '../classes/Result.php';
require_once '../classes/CSVManager.php';

class AdminController {
    private $conn;
    private $auth;
    private $quiz;
    private $question;
    private $result;
    private $csvManager;

    public function __construct($db) {
        $this->conn = $db;
        $this->auth = new Auth($db);
        $this->quiz = new Quiz($db);
        $this->question = new Question($db);
        $this->result = new Result($db);
        $this->csvManager = new CSVManager($db);
    }

    // --- Vérification admin ---
    public function verifierAdmin() {
        $user_id = $this->auth->verifierSession();
        if (!$user_id || !$this->auth->estAdmin($user_id)) {
            header('Location: ' . BASE_URL . '/pages/login.php');
            exit();
        }
        return $user_id;
    }

    // --- Statistiques générales ---
    public function getTotalParticipants() {
        $this->verifierAdmin();
        $query = "SELECT COUNT(DISTINCT user_id) as total FROM results";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function getMoyenneGenerale() {
        $this->verifierAdmin();
        $query = "SELECT AVG(score) as moyenne FROM results";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['moyenne'] ?? 0;
    }

    public function getStatistiquesQuiz($quiz_id) {
        try {
            $query = "SELECT COUNT(*) as participants, AVG(score) as moyenne 
                     FROM results 
                     WHERE quiz_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$quiz_id]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'participants' => (int)$stats['participants'],
                'moyenne' => round($stats['moyenne'] ?? 0, 2)
            ];
        } catch (Exception $e) {
            return ['participants' => 0, 'moyenne' => 0];
        }
    }

    // --- Quiz ---
    public function creerQuiz($titre, $description) {
        $this->verifierAdmin();
        return $this->quiz->creer($titre, $description);
    }

    public function modifierQuiz($id, $titre, $description) {
        $this->verifierAdmin();
        return $this->quiz->modifier($id, $titre, $description);
    }

    public function supprimerQuiz($id) {
        $this->verifierAdmin();
        return $this->quiz->supprimer($id);
    }

    public function getTousLesQuiz() {
        $this->verifierAdmin();
        return $this->quiz->getAllQuiz();
    }

    // --- Questions ---
    public function ajouterQuestion($quiz_id, $question_text, $bonne_reponse, $mauvaises_reponses, $image = null) {
        $this->verifierAdmin();
        return $this->question->ajouter($quiz_id, $question_text, $bonne_reponse, $mauvaises_reponses, $image);
    }

    public function modifierQuestion($id, $question_text, $bonne_reponse, $mauvaises_reponses, $image = null) {
        $this->verifierAdmin();
        return $this->question->modifier($id, $question_text, $bonne_reponse, $mauvaises_reponses, $image);
    }

    public function supprimerQuestion($id) {
        $this->verifierAdmin();
        return $this->question->supprimer($id);
    }

    public function getQuestionById($id) {
        return $this->question->getById($id);
    }

    public function getQuestionsByQuiz($quiz_id) {
        $this->verifierAdmin();
        return $this->question->getQuestionsByQuiz($quiz_id);
    }

    // --- Utilisateurs ---
    public function getTousLesUtilisateurs() {
        $this->verifierAdmin();
        $query = "SELECT id, nom, email, role, date_inscription FROM users ORDER BY date_inscription DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function changerRole($user_id, $role) {
        $this->verifierAdmin();
        $query = "UPDATE users SET role = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$role, $user_id]);
    }

    public function supprimerUtilisateur($user_id) {
        $this->verifierAdmin();
        $query = "DELETE FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$user_id]);
    }

    // --- Import / Export CSV ---
    public function importerQuestions($quiz_id, $fichier_csv) {
        $this->verifierAdmin();
        return $this->csvManager->importerQuestions($fichier_csv, $quiz_id);
    }

    public function exporterResultats($quiz_id = null) {
        $this->verifierAdmin();
        return $this->csvManager->exporterResultats($quiz_id);
    }
}
?>