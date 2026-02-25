<?php
session_start();
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../classes/Result.php';
require_once '../classes/Auth.php';

header('Content-Type: application/json');

// Connexion BDD
$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

// Vérifie la session utilisateur
$user_id = $auth->verifierSession();
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit();
}

// Vérifie les données reçues
if (!isset($_POST['quiz_id']) || !isset($_POST['score'])) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit();
}

$quiz_id = (int) $_POST['quiz_id'];
$score = (int) $_POST['score'];

// Enregistre le résultat
$result = new Result($db);
$success = $result->enregistrer($user_id, $quiz_id, $score);

echo json_encode(['success' => $success]);
?>