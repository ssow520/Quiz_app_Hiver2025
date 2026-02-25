<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/Question.php';
require_once '../classes/Result.php';

header('Content-Type: application/json');
session_start();

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

// Vérifie que l'utilisateur est connecté
if (!$auth->verifierSession()) {
    echo json_encode(['error' => 'Non autorisé']);
    exit();
}

// Vérifie que les données sont présentes
if (isset($_POST['question_id']) && isset($_POST['reponse'])) {
    $question = new Question($db);
    $question_data = $question->getQuestion($_POST['question_id']);

    if ($question_data) {
        // Vérifie si la réponse est correcte
        $correct = ($_POST['reponse'] === $question_data['bonne_reponse']);

        echo json_encode([
            'success' => true,
            'correct' => $correct,
            'bonne_reponse' => $question_data['bonne_reponse'],
            'message' => $correct ? 'Bonne réponse !' : 'Mauvaise réponse.'
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Question non trouvée']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Données manquantes']);
}
?>