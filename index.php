<?php
// Démarrer la session
session_start();

// Inclure les fichiers de configuration
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'classes/Auth.php';

// Initialiser la connexion à la base de données
$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

// Vérifier si l'utilisateur est déjà connecté
if ($auth->verifierSession()) {
    // Rediriger vers le tableau de bord
    header('Location: ' . BASE_URL . '/pages/dashboard.php');
    exit();
}

// Rediriger vers la page de connexion
header('Location: ' . BASE_URL . '/pages/login.php');
exit();
?>