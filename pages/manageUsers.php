<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../classes/AdminController.php';

$database = new Database();
$db = $database->getConnection();
$adminController = new AdminController($db);

// Vérifier que l'utilisateur est admin
$adminController->verifierAdmin();

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'change_role':
            $result = $adminController->changerRole($_POST['user_id'], $_POST['role']);
            $message = $result ? 'Rôle modifié avec succès' : 'Erreur lors de la modification';
            break;

        case 'delete_user':
            $result = $adminController->supprimerUtilisateur($_POST['user_id']);
            $message = $result ? 'Utilisateur supprimé avec succès' : 'Erreur lors de la suppression';
            break;
    }
}

// Récupérer la liste des utilisateurs
$users = $adminController->getTousLesUtilisateurs();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - Admin</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
<div class="auth-container" style="max-width:1000px; margin:50px auto;">
    <header style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h1>Gestion des Utilisateurs</h1>
        <a href="admin_dashboard.php" class="btn">Retour au tableau de bord</a>
    </header>

    <?php if (isset($message)): ?>
        <div class="alert <?php echo $result ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="users-list">
        <?php if (!empty($users)): ?>
            <div class="table-container">
                <table class="quiz-results">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Date d'inscription</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['nom']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <form method="POST" class="role-form">
                                        <input type="hidden" name="action" value="change_role">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <select name="role" onchange="this.form.submit()">
                                            <option value="participant" <?php echo $user['role'] === 'participant' ? 'selected' : ''; ?>>
                                                Participant
                                            </option>
                                            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>
                                                Admin
                                            </option>
                                        </select>
                                    </form>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($user['date_inscription'])); ?></td>
                                <td class="item-actions">
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn delete" 
                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                            Supprimer
                                        </button>
                                    </form>
                                    <a href="view_user_results.php?user_id=<?php echo $user['id']; ?>" class="btn">
                                        Voir les résultats
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>Aucun utilisateur n'est enregistré.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>