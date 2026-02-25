<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../controllers/AdminController.php';

$database = new Database();
$db = $database->getConnection();
$adminController = new AdminController($db);

// Vérifier que l'utilisateur est admin
$adminController->verifierAdmin();

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'create':
            $result = $adminController->creerQuiz($_POST['titre'], $_POST['description']);
            $message = $result ? 'Quiz créé avec succès' : 'Erreur lors de la création';
            break;
        case 'edit':
            $result = $adminController->modifierQuiz($_POST['quiz_id'], $_POST['titre'], $_POST['description']);
            $message = $result ? 'Quiz modifié avec succès' : 'Erreur lors de la modification';
            break;
        case 'delete':
            $result = $adminController->supprimerQuiz($_POST['quiz_id']);
            $message = $result ? 'Quiz supprimé avec succès' : 'Erreur lors de la suppression';
            break;
    }
}

// Récupérer tous les quiz
$quizList = $adminController->getTousLesQuiz();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Quiz - Admin</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <div class="auth-container" style="max-width:900px; margin:50px auto;">
        <header style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
            <h1>Gestion des Quiz</h1>
            <a href="adminDashboard.php" class="btn">Retour au tableau de bord</a>
        </header>

        <?php if (isset($message)): ?>
            <div class="alert <?php echo $result ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Formulaire de création -->
        <section class="create-quiz" style="margin-bottom:40px;">
            <h2>Créer un nouveau quiz</h2>
            <form method="POST" class="form-profile">
                <input type="hidden" name="action" value="create">
                <div class="form-group">
                    <label>Titre:</label>
                    <input type="text" name="titre" required>
                </div>
                <div class="form-group">
                    <label>Description:</label>
                    <textarea name="description" required></textarea>
                </div>
                <button type="submit" class="btn auth-btn">Créer le quiz</button>
            </form>
        </section>

        <!-- Liste des quiz existants -->
        <section class="quiz-list">
            <h2>Quiz existants</h2>
            <?php if (!empty($quizList)): ?>
                <div class="table-container">
                    <table class="quiz-results">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Description</th>
                                <th>Date de création</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($quizList as $quiz): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($quiz['titre']); ?></td>
                                    <td><?php echo htmlspecialchars($quiz['description']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($quiz['date_creation'])); ?></td>
                                    <td class="item-actions">
                                        <a href="editQuiz.php?id=<?php echo $quiz['id']; ?>" class="btn edit">Modifier</a>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="quiz_id" value="<?php echo $quiz['id']; ?>">
                                            <button type="submit" class="btn delete" 
                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce quiz ?')">
                                                Supprimer
                                            </button>
                                        </form>
                                        <a href="manageQuestions.php?quiz_id=<?php echo $quiz['id']; ?>" class="btn">Gérer les questions</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>Aucun quiz n'a été créé.</p>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>