<?php
require_once '../classes/Auth.php';
require_once '../classes/User.php';
require_once '../classes/Result.php';
// controllers/UserController.php
class UserController {
    private $conn;
    private $auth;
    private $user;
    private $result;
    
    public function __construct($db) {
        $this->conn = $db;
        $this->auth = new Auth($db);
        $this->user = new User($db);
        $this->result = new Result($db);
    }
    
    public function verifierAuthentification() {
        $user_id = $this->auth->verifierSession();
        if (!$user_id) {
            header('Location: ' . BASE_URL . '/pages/login.php');
            exit();
        }
        return $user_id;
    }
    
    public function afficherTableauDeBord() {
        $user_id = $this->verifierAuthentification();
        $user_info = $this->user->getUser($user_id);
        $resultats = $this->result->getResultatsUtilisateur($user_id);
        
        return [
            'user' => $user_info,
            'resultats' => $resultats
        ];
    }
    
    public function modifierProfil($nom, $email, $role) {
        $user_id = $this->verifierAuthentification();
        
        try {
            if ($this->user->modifier($user_id, $nom, $email, $role)) {
                return [
                    'success' => true,
                    'message' => 'Profil mis à jour avec succès'
                ];
            }
            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour'
            ];
        } catch(Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur système'
            ];
        }
    }
    
    public function getHistoriqueQuiz() {
        $user_id = $this->verifierAuthentification();
        return $this->result->getResultatsUtilisateur($user_id);
    }
    
    public function getStatistiquesUtilisateur() {
        $user_id = $this->verifierAuthentification();
        $resultats = $this->result->getResultatsUtilisateur($user_id);
        
        $total_quiz = count($resultats);
        $score_total = 0;
        $meilleur_score = 0;
        
        foreach ($resultats as $resultat) {
            $score_total += $resultat['score'];
            $meilleur_score = max($meilleur_score, $resultat['score']);
        }
        
        return [
            'total_quiz' => $total_quiz,
            'moyenne' => $total_quiz > 0 ? $score_total / $total_quiz : 0,
            'meilleur_score' => $meilleur_score
        ];
    }
}
?>