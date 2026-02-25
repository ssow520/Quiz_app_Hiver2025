<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/Question.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

// Vérifier l'authentification
if (!$auth->verifierSession()) {
    echo json_encode(['error' => 'Non autorisé']);
    exit();
}

if (isset($_POST['question_id'])) {
    $question = new Question($db);
    $question_data = $question->getQuestion($_POST['question_id']);
    
    if ($question_data) {
        $bonne_reponse = $question_data['bonne_reponse'];
        // Retourner la première lettre comme indice
        $hint = mb_substr($bonne_reponse, 0, 1, 'UTF-8');
        
        echo json_encode([
            'success' => true,
            'hint' => $hint,
            'message' => 'La réponse commence par : ' . $hint
        ]);
    } else {
        echo json_encode(['error' => 'Question non trouvée']);
    }
} else {
    echo json_encode(['error' => 'ID de question manquant']);
}
?>