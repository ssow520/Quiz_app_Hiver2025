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

// Récupérer l'ID de la question
$question_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$question_id) {
    header('Location: manageQuestions.php');
    exit();
}

// Récupérer les informations de la question
$question = $adminController->getQuestionById($question_id);

if (!$question) {
    header('Location: manageQuestions.php');
    exit();
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $adminController->modifierQuestion(
        $question_id,
        $_POST['question_text'],
        $_POST['bonne_reponse'],
        $_POST['mauvaises_reponses']
    );

    if ($result) {
        header('Location: manageQuestions.php?quiz_id=' . $question['quiz_id'] . '&success=1');
        exit();
    } else {
        $error = "Erreur lors de la modification de la question";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la Question - Admin</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <div class="auth-container" style="max-width:700px; margin:50px auto;">
        <header style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
            <h1>Modifier la Question</h1>
            <a href="manageQuestions.php?quiz_id=<?php echo $question['quiz_id']; ?>" class="btn">Retour</a>
        </header>

        <?php if (isset($error)): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <section class="edit-question">
            <form method="POST" class="form-profile">
                <div class="form-group">
                    <label>Question:</label>
                    <textarea name="question_text" required rows="3"><?php 
                        echo htmlspecialchars($question['question_text']); 
                    ?></textarea>
                </div>

                <div class="form-group">
                    <label>Bonne réponse:</label>
                    <input type="text" name="bonne_reponse" required
                           value="<?php echo htmlspecialchars($question['bonne_reponse']); ?>">
                </div>

                <div class="form-group">
                    <label>Mauvaises réponses (séparées par des points-virgules):</label>
                    <textarea name="mauvaises_reponses" required rows="2"><?php 
                        echo htmlspecialchars($question['mauvaises_reponses']); 
                    ?></textarea>
                </div>

                <div class="form-actions" style="margin-top:20px;">
                    <button type="submit" class="btn auth-btn">Enregistrer les modifications</button>
                    <a href="manageQuestions.php?quiz_id=<?php echo $question['quiz_id']; ?>" class="btn" style="margin-left:10px;">Annuler</a>
                </div>
            </form>
        </section>
    </div>
</body>
</html>