<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../controllers/AuthController.php';

$database = new Database();
$db = $database->getConnection();
$authController = new AuthController($db);

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember = isset($_POST['remember']);

    $result = $authController->connexion($email, $password, $remember);

    if ($result['success']) {
        header('Location: dashboard.php');
        exit();
    } else {
        $error = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Quiz App</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <div class="auth-container">
        <h1>Connexion</h1>

        <?php if(isset($_GET['success'])): ?>
            <div class="alert success">Inscription réussie! Vous pouvez vous connecter.</div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember" <?php echo isset($_POST['remember']) ? 'checked' : ''; ?>> Se souvenir de moi
                </label>
            </div>

            <button type="submit" class="btn">Se connecter</button>
        </form>

        <p>Pas encore inscrit? <a href="register.php">Inscrivez-vous</a></p>
    </div>
</body>
</html>