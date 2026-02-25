<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../controllers/AdminController.php';

$database = new Database();
$db = $database->getConnection();
$adminController = new AdminController($db);

$adminController->verifierAdmin();

$totalQuiz = count($adminController->getTousLesQuiz());
$statsGlobales = [
    'total_quiz' => $totalQuiz,
    'total_participants' => $adminController->getTotalParticipants(),
    'moyenne_generale' => $adminController->getMoyenneGenerale(),
];
$quizList = $adminController->getTousLesQuiz();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tableau de Bord Admin - Quiz App</title>
<link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
<div class="admin-dashboard">
    <header class="admin-header">
        <h1>Tableau de Bord Admin</h1>
        <div class="header-links">
            <a href="dashboard.php" class="btn">Retour</a>
            <a href="logout.php" class="btn logout">Déconnexion</a>
        </div>
    </header>

    <div class="dashboard-stats admin-stats">
        <div class="stat-card">
            <h3>Total Quiz</h3>
            <p class="stat-number"><?php echo $statsGlobales['total_quiz']; ?></p>
            <a href="manageQuiz.php" class="btn">Gérer les quiz</a>
        </div>
        <div class="stat-card">
            <h3>Total Participants</h3>
            <p class="stat-number"><?php echo $statsGlobales['total_participants']; ?></p>
        </div>
        <div class="stat-card">
            <h3>Moyenne Générale</h3>
            <p class="stat-number"><?php echo number_format($statsGlobales['moyenne_generale'], 1); ?>%</p>
        </div>
    </div>

    <div class="dashboard-content admin-actions">
        <section class="quick-actions">
            <h2>Actions Rapides</h2>
            <div class="action-buttons">
                <a href="manageQuiz.php" class="btn">Créer un nouveau quiz</a>
                <a href="../import_export/import_questions.php" class="btn">Importer des questions</a>
                <a href="../import_export/export_results.php" class="btn">Exporter les résultats</a>
            </div>
        </section>

        <section class="recent-activity">
            <h2>Activité Récente</h2>
            <div class="activity-list">
                <?php foreach ($quizList as $quiz): 
                    $stats = $adminController->getStatistiquesQuiz($quiz['id']);
                ?>
                <div class="activity-item">
                    <h3><?php echo htmlspecialchars($quiz['titre']); ?></h3>
                    <p>Participants: <?php echo $stats['participants']; ?></p>
                    <p>Moyenne: <?php echo number_format($stats['moyenne'], 1); ?>%</p>
                    <div class="item-actions">
                        <a href="manageQuiz.php?id=<?php echo $quiz['id']; ?>" class="btn edit">Modifier</a>
                        <a href="results.php?quiz_id=<?php echo $quiz['id']; ?>" class="btn">Résultats</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

</div>
</body>
</html>