<?php
require_once '../classes/User.php';
require_once '../classes/Auth.php';
require_once '../classes/Captcha.php';

class AuthController {
    private $conn;
    private $user;
    private $auth;
    private $captcha;

    public function __construct($db) {
        $this->conn = $db;
        $this->user = new User($db);
        $this->auth = new Auth($db);
        $this->captcha = new Captcha($db);
    }

    public function inscription($nom, $email, $password, $captcha_code, $role) {
        try {
            if (!$this->captcha->verifierCaptcha($captcha_code)) {
                return ['success' => false, 'message' => 'Code CAPTCHA incorrect'];
            }

            if ($this->user->inscription($nom, $email, $password, $role)) {
                return ['success' => true];
            }

            return ['success' => false, 'message' => 'Email déjà utilisé'];

        } catch(Exception $e) {
            return ['success' => false, 'message' => 'Erreur lors de l\'inscription'];
        }
    }

    public function connexion($email, $password, $remember = false) {
        try {
            $userData = $this->user->connexion($email, $password);

            if ($userData) {
                $this->auth->creerSession($userData['id'], $remember);
                return ['success' => true, 'user' => $userData];
            }

            return ['success' => false, 'message' => 'Email ou mot de passe incorrect'];

        } catch(Exception $e) {
            return ['success' => false, 'message' => 'Erreur lors de la connexion'];
        }
    }

    public function deconnexion() {
        $this->auth->deconnexion();
        return ['success' => true];
    }

    public function verifierAuthentification() {
        $user_id = $this->auth->verifierSession();

        if (!$user_id) {
            header('Location: ' . BASE_URL . '/pages/login.php');
            exit();
        }

        return $user_id;
    }

    public function getUserInfo($user_id) {
        return $this->user->getUser($user_id);
    }

    public function modifierProfil($user_id, $nom, $email, $role) {
        try {
            if ($this->user->modifier($user_id, $nom, $email, $role)) {
                return ['success' => true];
            }

            return ['success' => false, 'message' => 'Erreur lors de la modification'];

        } catch(Exception $e) {
            return ['success' => false, 'message' => 'Erreur système'];
        }
    }
}
?>