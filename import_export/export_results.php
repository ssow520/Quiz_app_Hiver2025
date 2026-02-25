<?php
session_start();
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../classes/Auth.php';

// Initialiser la connexion à la base de données
$database = new Database();
$db = $database->getConnection();

// Vérifier si l'utilisateur est connecté et est admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

// Vérifier si l'utilisateur est admin
$query = "SELECT role FROM users WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user['role'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

// Préparer l'export CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=quiz_results.csv');

// Créer le fichier CSV
$output = fopen('php://output', 'w');

// En-têtes CSV
fputcsv($output, [
    'ID', 
    'Utilisateur', 
    'Quiz', 
    'Score', 
    'Date de completion',
    'Temps total (minutes)',
    'Questions correctes',
    'Questions totales'
]);

// Requête pour obtenir tous les résultats
$query = "SELECT r.id,
          u.nom as user_name,
          q.titre as quiz_title,
          r.score,
          r.date,
          r.temps_total,
          r.questions_correctes,
          q.nombre_questions
          FROM results r
          JOIN users u ON r.user_id = u.id
          JOIN quiz q ON r.quiz_id = q.id
          ORDER BY r.date DESC";

$stmt = $db->prepare($query);
$stmt->execute();

// Écrire les données
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Convertir le temps total en minutes
    $temps_minutes = round($row['temps_total'] / 60, 2);
    
    fputcsv($output, [
        $row['id'],
        $row['user_name'],
        $row['quiz_title'],
        $row['score'] . '%',
        date('Y-m-d H:i:s', strtotime($row['date'])),
        $temps_minutes,
        $row['questions_correctes'],
        $row['nombre_questions']
    ]);
}

// Fermer le fichier
fclose($output);
exit();
?>