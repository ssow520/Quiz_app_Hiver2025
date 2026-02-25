<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../controllers/AuthController.php';
require_once '../classes/Captcha.php';

$database = new Database();
$db = $database->getConnection();
$authController = new AuthController($db);
$captcha = new Captcha($db);

// Générer un nouveau CAPTCHA
$captchaCode = $captcha->genererCaptcha();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $authController->inscription(
        $_POST['nom'],
        $_POST['email'],
        $_POST['password'],
        $_POST['captcha_code'],
        $_POST['role']
    );
    
    if ($result['success']) {
        header('Location: login.php?success=1');
        exit();
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Inscription - Quiz App</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <div class="auth-container">
        <h1>Inscription</h1>
        
        <?php if(isset($error)): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <div class="form-group">
                <label>Nom:</label>
                <input type="text" name="nom" required
                        value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <div class="form-group">
                <label>Rôle:</label>
                <select name="role" required>
                    <option value="participant" <?php echo (isset($_POST['role']) && $_POST['role'] === 'participant') ? 'selected' : ''; ?>>Participant</option>
                    <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : ''; ?>>Administrateur</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Mot de passe:</label>
                <input type="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label>Code CAPTCHA:</label>
                <?php 
                    echo '<img src="data:image/png;base64,' . $captcha->getCaptchaImage($captchaCode) . '" alt="CAPTCHA">';
                ?>
                <input type="text" name="captcha_code" required placeholder="Entrez le code">
                <input type="hidden" name="captcha_id" value="<?php echo $captchaCode; ?>">
            </div>
            
            <button type="submit" class="btn">S'inscrire</button>
        </form>
        
        <p>Déjà inscrit? <a href="login.php">Connectez-vous</a></p>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Validation côté client
        document.querySelector('.auth-form').addEventListener('submit', function(e) {
            const password = document.querySelector('input[name="password"]').value;
            if (password.length < 6) {
                e.preventDefault();
                alert('Le mot de passe doit contenir au moins 6 caractères.');
            }
        });
    });
    </script>
</body>
</html>