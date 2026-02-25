<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../controllers/UserController.php';

$database = new Database();
$db = $database->getConnection();
$userController = new UserController($db);

$dashboardData = $userController->afficherTableauDeBord();
$user = $dashboardData['user'];
$resultats = $dashboardData['resultats'];
$stats = $userController->getStatistiquesUtilisateur();

$isAdmin = ($user['role'] === 'admin');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Quiz App</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Bienvenue, <?php echo htmlspecialchars($user['nom']); ?></h1>
            <div class="header-links">
                <?php if ($isAdmin): ?>
                    <a href="adminDashboard.php" class="btn admin">Tableau Admin</a>
                <?php endif; ?>
                <a href="logout.php" class="btn logout">Déconnexion</a>
            </div>
        </header>

        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>Quiz complétés</h3>
                <p class="stat-number"><?php echo $stats['total_quiz']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Score moyen</h3>
                <p class="stat-number"><?php echo number_format($stats['moyenne'], 1); ?>%</p>
            </div>
            <div class="stat-card">
                <h3>Meilleur score</h3>
                <p class="stat-number"><?php echo $stats['meilleur_score']; ?>%</p>
            </div>
        </div>

        <div class="dashboard-content">
            <section class="profile-section">
                <h2>Mon profil</h2>
                <div class="profile-card">
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Date d'inscription:</strong> <?php echo date('d/m/Y', strtotime($user['date_inscription'])); ?></p>
                    <a href="edit_profile.php" class="btn">Modifier le profil</a>
                </div>
            </section>

            <section class="quiz-section">
                <h2>Mes quiz récents</h2>
                <?php if (!empty($resultats)): ?>
                    <div class="table-container">
                        <table class="quiz-results">
                            <thead>
                                <tr>
                                    <th>Quiz</th>
                                    <th>Score</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($resultats as $resultat): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($resultat['quiz_titre']); ?></td>
                                    <td><?php echo $resultat['score']; ?>%</td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($resultat['date'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>Vous n'avez pas encore participé à un quiz.</p>
                <?php endif; ?>
                <a href="quizList.php" class="btn">Voir tous les quiz disponibles</a>
            </section>
        </div>
    </div>
</body>
</html>