<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/User.php';

// Initialiser la base de données
$database = new Database();
$db = $database->getConnection();

// Vérifier si l'utilisateur est connecté
$auth = new Auth($db);
if (!$auth->verifierSession()) {
    header('Location: login.php');
    exit();
}

// Récupérer les informations de l'utilisateur
$user_id = $_SESSION['user_id'];
$query = "SELECT nom, email, role FROM users WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$message = '';
$error = '';

// Traiter le formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    // Validation des champs
    if (empty($nom) || empty($email)) {
        $error = "Le nom et l'email sont obligatoires";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format d'email invalide";
    } else {
        try {
            // Vérifier si l'email existe déjà (sauf pour l'utilisateur actuel)
            $check_email = "SELECT id FROM users WHERE email = ? AND id != ?";
            $stmt = $db->prepare($check_email);
            $stmt->execute([$email, $user_id]);
            if ($stmt->rowCount() > 0) {
                $error = "Cet email est déjà utilisé";
            } else {
                // Mise à jour des informations de base
                $update_query = "UPDATE users SET nom = ?, email = ?, role = ? WHERE id = ?";
                $stmt = $db->prepare($update_query);
                $stmt->execute([$nom, $email, $role, $user_id]);

                // Mise à jour du mot de passe si fourni
                if (!empty($current_password) && !empty($new_password)) {
                    if ($new_password !== $confirm_password) {
                        $error = "Les nouveaux mots de passe ne correspondent pas";
                    } else {
                        // Vérifier l'ancien mot de passe
                        $check_password = "SELECT mot_de_passe FROM users WHERE id = ?";
                        $stmt = $db->prepare($check_password);
                        $stmt->execute([$user_id]);
                        $current_hash = $stmt->fetchColumn();

                        if (password_verify($current_password, $current_hash)) {
                            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                            $update_password = "UPDATE users SET mot_de_passe = ? WHERE id = ?";
                            $stmt = $db->prepare($update_password);
                            $stmt->execute([$new_hash, $user_id]);
                            $message = "Profil mis à jour avec succès";
                        } else {
                            $error = "Mot de passe actuel incorrect";
                        }
                    }
                } else {
                    $message = "Profil mis à jour avec succès";
                }
            }
        } catch (PDOException $e) {
            $error = "Une erreur est survenue lors de la mise à jour";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le profil - Quiz App</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <div class="auth-container" style="max-width:600px; margin:50px auto;">
        <h1>Modifier mon profil</h1>
        
        <?php if (!empty($error)): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($message)): ?>
            <div class="alert success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST" class="form-profile">
            <div class="form-group">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="form-group">
                <label for="role">Rôle</label>
                <select name="role" id="role" required>
                    <option value="participant" <?php echo ($user['role'] === 'participant') ? 'selected' : ''; ?>>Participant</option>
                    <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Administrateur</option>
                </select>
            </div>

            <h2>Changer le mot de passe</h2>
            <div class="form-group">
                <label for="current_password">Mot de passe actuel</label>
                <input type="password" id="current_password" name="current_password">
            </div>

            <div class="form-group">
                <label for="new_password">Nouveau mot de passe</label>
                <input type="password" id="new_password" name="new_password">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password">
            </div>

            <div class="form-actions" style="margin-top:20px;">
                <button type="submit" class="btn auth-btn">Enregistrer les modifications</button>
                <a href="dashboard.php" class="btn" style="margin-left:10px;">Retour au tableau de bord</a>
            </div>
        </form>
    </div>
</body>
</html>