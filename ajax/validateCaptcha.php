<?php
// ajax/validate_captcha.php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../classes/Captcha.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();
$captcha = new Captcha($db);

if (isset($_POST['code'])) {
    $code = $_POST['code'];
    
    if ($captcha->verifierCaptcha($code)) {
        echo json_encode([
            'success' => true,
            'message' => 'Code CAPTCHA valide'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Code CAPTCHA invalide'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Code CAPTCHA manquant'
    ]);
}
?>