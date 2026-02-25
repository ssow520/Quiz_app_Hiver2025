<?php
session_start();
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../controllers/QuizController.php';

$database = new Database();
$db = $database->getConnection();
$quizController = new QuizController($db);

// Récupérer la liste des quiz disponibles
$quizDisponibles = $quizController->getQuizDisponibles();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Quiz disponibles - Quiz App</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <div class="quiz-list-container">
        <header>
            <h1>Quiz disponibles</h1>
            <a href="dashboard.php" class="btn back">Retour au tableau de bord</a>
        </header>

        <div class="quiz-grid">
            <?php if (!empty($quizDisponibles)): ?>
                <?php foreach ($quizDisponibles as $quiz): ?>
                    <div class="quiz-card">
                        <h2><?php echo htmlspecialchars($quiz['titre']); ?></h2>
                        <p class="description"><?php echo htmlspecialchars($quiz['description']); ?></p>
                        <div class="quiz-info">
                            <span class="date">Créé le: <?php echo date('d/m/Y', strtotime($quiz['date_creation'])); ?></span>
                        </div>
                        <div class="quiz-actions">
                            <a href="takeQuiz.php?id=<?php echo $quiz['id']; ?>" class="btn start">
                                Commencer le quiz
                            </a>
                            <?php if (isset($quiz['dernier_score'])): ?>
                                <p class="last-score">
                                    Dernier score: <?php echo $quiz['dernier_score']; ?>%
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-quiz">
                    <p>Aucun quiz n'est disponible pour le moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animation des cartes au survol
        const quizCards = document.querySelectorAll('.quiz-card');
        quizCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    });
</script>

</body>
</html>