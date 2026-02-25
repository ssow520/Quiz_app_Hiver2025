<?php
session_start();
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../controllers/AdminController.php';

// Initialiser la connexion
$database = new Database();
$db = $database->getConnection();
$adminController = new AdminController($db);

// Récupérer l'ID du quiz
$quiz_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$quiz_id) {
    header('Location: manageQuiz.php');
    exit();
}

// Récupérer les informations du quiz
$query = "SELECT * FROM quiz WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$quiz) {
    header('Location: manageQuiz.php');
    exit();
}

// Traiter le formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);
    
    if (empty($titre)) {
        $error = "Le titre est obligatoire";
    } else {
        try {
            $query = "UPDATE quiz SET titre = ?, description = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            if ($stmt->execute([$titre, $description, $quiz_id])) {
                header('Location: manageQuiz.php?success=1');
                exit();
            } else {
                $error = "Erreur lors de la modification";
            }
        } catch(PDOException $e) {
            $error = "Erreur de base de données: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Quiz - Admin</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <div class="auth-container" style="max-width:700px; margin:50px auto;">
        <header style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
            <h1>Modifier le Quiz</h1>
            <a href="manageQuiz.php" class="btn">Retour</a>
        </header>

        <?php if (isset($error)): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" class="form-profile">
            <div class="form-group">
                <label for="titre">Titre du Quiz</label>
                <input type="text" id="titre" name="titre"
                       value="<?php echo htmlspecialchars($quiz['titre']); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4"><?php 
                    echo htmlspecialchars($quiz['description']); 
                ?></textarea>
            </div>

            <div class="form-actions" style="margin-top:20px;">
                <button type="submit" class="btn auth-btn">Enregistrer les modifications</button>
                <a href="manageQuiz.php" class="btn" style="margin-left:10px;">Annuler</a>
            </div>
        </form>
    </div>
</body>
</html>