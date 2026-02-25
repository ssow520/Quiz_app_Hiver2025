<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../controllers/UserController.php';

$database = new Database();
$db = $database->getConnection();
$userController = new UserController($db);

// Vérifier l'authentification
$user_id = $userController->verifierAuthentification();

// Récupérer l'historique des quiz
$resultats = $userController->getHistoriqueQuiz();

// Récupérer les statistiques
$stats = $userController->getStatistiquesUtilisateur();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mes Résultats - Quiz App</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <div class="results-container">
        <header>
            <h1>Mes Résultats</h1>
            <a href="dashboard.php" class="btn back">Retour au tableau de bord</a>
        </header>

        <div class="stats-summary">
            <div class="stat-box">
                <h3>Total des quiz complétés</h3>
                <p class="stat-number"><?php echo $stats['total_quiz']; ?></p>
            </div>
            <div class="stat-box">
                <h3>Score moyen</h3>
                <p class="stat-number"><?php echo number_format($stats['moyenne'], 1); ?>%</p>
            </div>
            <div class="stat-box">
                <h3>Meilleur score</h3>
                <p class="stat-number"><?php echo $stats['meilleur_score']; ?>%</p>
            </div>
        </div>

        <div class="results-history">
            <h2>Historique détaillé</h2>
            <?php if (!empty($resultats)): ?>
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Quiz</th>
                            <th>Score</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resultats as $resultat): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($resultat['quiz_titre']); ?></td>
                                <td>
                                    <div class="score-bar">
                                        <div class="score-fill" style="width: <?php echo $resultat['score']; ?>%"></div>
                                        <span><?php echo $resultat['score']; ?>%</span>
                                    </div>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($resultat['date'])); ?></td>
                                <td>
                                    <a href="takeQuiz.php?id=<?php echo $resultat['quiz_id']; ?>" 
                                       class="btn retry">Réessayer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-results">Vous n'avez pas encore participé à un quiz.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animation des barres de score
        const scoreBars = document.querySelectorAll('.score-fill');
        scoreBars.forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0';
            setTimeout(() => {
                bar.style.width = width;
            }, 100);
        });
    });
    </script>
</body>
</html>