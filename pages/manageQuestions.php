<?php
session_start();
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../controllers/AdminController.php';

$database = new Database();
$db = $database->getConnection();
$adminController = new AdminController($db);

// Vérifier que l'utilisateur est admin
$adminController->verifierAdmin();

// Récupérer l'ID du quiz
$quiz_id = isset($_GET['quiz_id']) ? $_GET['quiz_id'] : null;
if (!$quiz_id) {
    header('Location: manageQuiz.php');
    exit();
}

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'create':
            $result = $adminController->ajouterQuestion(
                $quiz_id,
                $_POST['question_text'],
                $_POST['bonne_reponse'],
                $_POST['mauvaises_reponses']
            );
            $message = $result ? 'Question créée avec succès' : 'Erreur lors de la création';
            break;
        case 'edit':
            $result = $adminController->modifierQuestion(
                $_POST['question_id'],
                $_POST['question_text'],
                $_POST['bonne_reponse'],
                $_POST['mauvaises_reponses']
            );
            $message = $result ? 'Question modifiée avec succès' : 'Erreur lors de la modification';
            break;
        case 'delete':
            $result = $adminController->supprimerQuestion($_POST['question_id']);
            $message = $result ? 'Question supprimée avec succès' : 'Erreur lors de la suppression';
            break;
    }
}

// Récupérer les informations du quiz
$quizzes = $adminController->getTousLesQuiz();
$quiz = null;
foreach ($quizzes as $q) {
    if ($q['id'] == $quiz_id) {
        $quiz = $q;
        break;
    }
}
$questions = $adminController->getQuestionsByQuiz($quiz_id);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Questions - <?php echo htmlspecialchars($quiz['titre']); ?></title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <div class="auth-container" style="max-width:900px; margin:50px auto;">
        <header style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
            <h1>Gestion des Questions - <?php echo htmlspecialchars($quiz['titre']); ?></h1>
            <a href="manageQuiz.php" class="btn">Retour aux quiz</a>
        </header>

        <?php if (isset($message)): ?>
            <div class="alert <?php echo $result ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Formulaire de création -->
        <section class="create-question" style="margin-bottom:40px;">
            <h2>Ajouter une nouvelle question</h2>
            <form method="POST" class="form-profile">
                <input type="hidden" name="action" value="create">
                <div class="form-group">
                    <label>Question:</label>
                    <textarea name="question_text" required></textarea>
                </div>
                <div class="form-group">
                    <label>Bonne réponse:</label>
                    <input type="text" name="bonne_reponse" required>
                </div>
                <div class="form-group">
                    <label>Mauvaises réponses (séparées par des points-virgules):</label>
                    <textarea name="mauvaises_reponses" required placeholder="réponse1; réponse2; réponse3"></textarea>
                </div>
                <button type="submit" class="btn auth-btn">Ajouter la question</button>
            </form>
        </section>

        <!-- Liste des questions existantes -->
        <section class="question-list">
            <h2>Questions existantes</h2>
            <?php if (!empty($questions)): ?>
                <div class="table-container">
                    <table class="quiz-results">
                        <thead>
                            <tr>
                                <th>Question</th>
                                <th>Bonne réponse</th>
                                <th>Mauvaises réponses</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($questions as $question): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($question['question_text']); ?></td>
                                    <td><?php echo htmlspecialchars($question['bonne_reponse']); ?></td>
                                    <td><?php echo htmlspecialchars($question['mauvaises_reponses']); ?></td>
                                    <td class="item-actions">
                                        <a href="edit_question.php?id=<?php echo $question['id']; ?>" class="btn edit">Modifier</a>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                                            <button type="submit" class="btn delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette question ?')">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>Aucune question n'a été créée pour ce quiz.</p>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>